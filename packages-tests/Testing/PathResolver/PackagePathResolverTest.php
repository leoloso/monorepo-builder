<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Tests\Testing\PathResolver;

use Symplify\MonorepoBuilder\Kernel\MonorepoBuilderKernel;
use Symplify\MonorepoBuilder\Testing\PathResolver\PackagePathResolver;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PackagePathResolverTest extends AbstractKernelTestCase
{
    private PackagePathResolver $packagePathResolver;

    protected function setUp(): void
    {
        $this->bootKernel(MonorepoBuilderKernel::class);
        $this->packagePathResolver = $this->getService(PackagePathResolver::class);
    }

    public function test(): void
    {
        $mainComposerJson = new SmartFileInfo(__DIR__ . '/PackagePathResolverTestSource/some_root/composer.json');

        $packageComposerJson = new SmartFileInfo(
            __DIR__ . '/PackagePathResolverTestSource/some_root/nested_packages/nested/composer.json'
        );

        $relativePathToLocalPackage = $this->packagePathResolver->resolveRelativePathToLocalPackage(
            $mainComposerJson,
            $packageComposerJson
        );

        $this->assertSame('../../nested_packages/nested', $relativePathToLocalPackage);

        $relativeFolderPathToLocalPackage = $this->packagePathResolver->resolveRelativeFolderPathToLocalPackage(
            $mainComposerJson,
            $packageComposerJson
        );

        $this->assertSame('../../', $relativeFolderPathToLocalPackage);

        $relativeDirectoryToRoot = $this->packagePathResolver->resolveRelativeDirectoryToRoot(
            $mainComposerJson,
            $packageComposerJson
        );

        $this->assertSame('nested_packages/nested', $relativeDirectoryToRoot);
    }
}
