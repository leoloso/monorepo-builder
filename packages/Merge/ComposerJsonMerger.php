<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Merge;

use Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\MonorepoBuilder\Merge\Configuration\MergedPackagesCollector;
use Symplify\MonorepoBuilder\Merge\Contract\ComposerKeyMergerInterface;
use Symplify\MonorepoBuilder\Merge\PathResolver\AutoloadPathNormalizer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Symplify\MonorepoBuilder\Tests\Merge\ComposerJsonMerger\ComposerJsonMergerTest
 */
final class ComposerJsonMerger
{
    /**
     * @param ComposerKeyMergerInterface[] $composerKeyMergers
     */
    public function __construct(
        private ComposerJsonFactory $composerJsonFactory,
        private MergedPackagesCollector $mergedPackagesCollector,
        private AutoloadPathNormalizer $autoloadPathNormalizer,
        private array $composerKeyMergers
    ) {
    }

    /**
     * @param SmartFileInfo[] $composerPackagesFileInfos
     */
    public function mergeFileInfos(array $composerPackagesFileInfos): ComposerJson
    {
        $mainComposerJson = $this->composerJsonFactory->createFromArray([]);

        foreach ($composerPackagesFileInfos as $composerPackageFileInfo) {
            $packageComposerJson = $this->composerJsonFactory->createFromFileInfo($composerPackageFileInfo);

            $this->mergeJsonToRootWithPackageFileInfo(
                $mainComposerJson,
                $packageComposerJson,
                $composerPackageFileInfo
            );
        }

        return $mainComposerJson;
    }

    public function mergeJsonToRoot(ComposerJson $mainComposerJson, ComposerJson $newComposerJson): void
    {
        $name = $newComposerJson->getName();
        if ($name !== null) {
            $this->mergedPackagesCollector->addPackage($name);
        }

        foreach ($this->composerKeyMergers as $composerKeyMerger) {
            $composerKeyMerger->merge($mainComposerJson, $newComposerJson);
        }
    }

    private function mergeJsonToRootWithPackageFileInfo(
        ComposerJson $mainComposerJson,
        ComposerJson $newComposerJson,
        SmartFileInfo $packageFileInfo
    ): void {
        // prepare paths before autolaod merging
        $this->autoloadPathNormalizer->normalizeAutoloadPaths($newComposerJson, $packageFileInfo);
        $this->mergeJsonToRoot($mainComposerJson, $newComposerJson);
    }
}
