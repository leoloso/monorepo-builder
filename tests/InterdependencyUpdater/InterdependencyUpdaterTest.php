<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Tests\InterdependencyUpdater;

use Symplify\MonorepoBuilder\DependencyUpdater;
use Symplify\MonorepoBuilder\Kernel\MonorepoBuilderKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class InterdependencyUpdaterTest extends AbstractKernelTestCase
{
    private DependencyUpdater $dependencyUpdater;

    private SmartFileSystem $smartFileSystem;

    protected function setUp(): void
    {
        $this->bootKernel(MonorepoBuilderKernel::class);

        $this->dependencyUpdater = $this->getService(DependencyUpdater::class);
        $this->smartFileSystem = $this->getService(SmartFileSystem::class);
    }

    protected function tearDown(): void
    {
        $this->smartFileSystem->copy(__DIR__ . '/Source/backup-first.json', __DIR__ . '/Source/first.json', true);
    }

    public function testVendor(): void
    {
        $this->dependencyUpdater->updateFileInfosWithVendorAndVersion(
            [new SmartFileInfo(__DIR__ . '/Source/first.json')],
            'symplify',
            '^5.0'
        );

        $this->assertFileEquals(__DIR__ . '/Source/expected-first-vendor.json', __DIR__ . '/Source/first.json');
    }

    public function testPackages(): void
    {
        $this->dependencyUpdater->updateFileInfosWithPackagesAndVersion(
            [new SmartFileInfo(__DIR__ . '/Source/first.json')],
            ['symplify/coding-standard'],
            '^6.0'
        );

        $this->assertFileEquals(__DIR__ . '/Source/expected-first-packages.json', __DIR__ . '/Source/first.json');
    }
}
