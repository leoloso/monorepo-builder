<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Package;

use Symplify\MonorepoBuilder\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\ValueObject\Package;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SymplifyKernel\Exception\ShouldNotHappenException;

final class PackageProvider
{
    public function __construct(
        private ComposerJsonProvider $composerJsonProvider,
        private JsonFileManager $jsonFileManager
    ) {
    }

    /**
     * @return Package[]
     */
    public function provide(): array
    {
        $packages = [];
        foreach ($this->composerJsonProvider->getPackagesComposerFileInfos() as $packagesComposerFileInfo) {
            $packageName = $this->detectNameFromFileInfo($packagesComposerFileInfo);

            $hasTests = file_exists($packagesComposerFileInfo->getRealPathDirectory() . '/tests');
            $packages[] = new Package($packageName, $hasTests);
        }

        usort(
            $packages,
            static fn (Package $firstPackage, Package $secondPackage): int => $firstPackage->getShortName() <=> $secondPackage->getShortName()
        );

        return $packages;
    }

    private function detectNameFromFileInfo(SmartFileInfo $smartFileInfo): string
    {
        $json = $this->jsonFileManager->loadFromFileInfo($smartFileInfo);

        if (! isset($json['name'])) {
            $errorMessage = sprintf(
                'Package "name" is missing in "composer.json" for "%s"',
                $smartFileInfo->getRelativeFilePathFromCwd()
            );
            throw new ShouldNotHappenException($errorMessage);
        }

        return (string) $json['name'];
    }
}
