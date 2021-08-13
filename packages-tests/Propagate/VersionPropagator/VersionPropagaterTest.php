<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Tests\Propagate\VersionPropagator;

use Iterator;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\MonorepoBuilder\Propagate\VersionPropagator;
use Symplify\MonorepoBuilder\Tests\Merge\ComposerJsonDecorator\AbstractComposerJsonDecoratorTest;
use Symplify\SmartFileSystem\SmartFileInfo;

final class VersionPropagaterTest extends AbstractComposerJsonDecoratorTest
{
    private VersionPropagator $versionPropagator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->versionPropagator = $this->getService(VersionPropagator::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $trioContent = $this->trioFixtureSplitter->splitFileInfo($fixtureFileInfo);

        $mainComposerJson = $this->composerJsonFactory->createFromString($trioContent->getFirstValue());
        $packageComposerJson = $this->createComposerJson($trioContent->getSecondValue());

        $this->versionPropagator->propagate($mainComposerJson, $packageComposerJson);

        $this->assertComposerJsonEquals($trioContent->getExpectedResult(), $packageComposerJson);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture', '*.json');
    }
}
