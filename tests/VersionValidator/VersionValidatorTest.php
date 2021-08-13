<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Tests\VersionValidator;

use Symfony\Component\Finder\Finder;
use Symplify\MonorepoBuilder\HttpKernel\MonorepoBuilderKernel;
use Symplify\MonorepoBuilder\VersionValidator;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class VersionValidatorTest extends AbstractKernelTestCase
{
    private VersionValidator $versionValidator;

    private FinderSanitizer $finderSanitizer;

    protected function setUp(): void
    {
        $this->bootKernel(MonorepoBuilderKernel::class);

        $this->versionValidator = $this->getService(VersionValidator::class);
        $this->finderSanitizer = $this->getService(FinderSanitizer::class);
    }

    public function test(): void
    {
        $finder = Finder::create()
            ->name('*.json')
            ->in(__DIR__ . '/Source');

        $fileInfos = $this->finderSanitizer->sanitize($finder);

        $conflictingPackageVersionsPerFile = $this->versionValidator->findConflictingPackageVersionsInFileInfos(
            $fileInfos
        );

        $this->assertArrayHasKey('some/package', $conflictingPackageVersionsPerFile);

        $firstJson = new SmartFileInfo(__DIR__ . DIRECTORY_SEPARATOR . 'Source' . DIRECTORY_SEPARATOR . 'first.json');
        $secondJson = new SmartFileInfo(__DIR__ . DIRECTORY_SEPARATOR . 'Source' . DIRECTORY_SEPARATOR . 'second.json');

        $expectedConflictingVersionsPerFile = [
            $firstJson->getRelativeFilePathFromCwd() => '^1.0',
            $secondJson->getRelativeFilePathFromCwd() => '^2.0',
        ];

        $this->assertSame($expectedConflictingVersionsPerFile, $conflictingPackageVersionsPerFile['some/package']);
    }
}
