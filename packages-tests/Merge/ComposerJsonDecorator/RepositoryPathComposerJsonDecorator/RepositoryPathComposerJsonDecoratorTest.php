<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Tests\Merge\ComposerJsonDecorator\RepositoryPathComposerJsonDecorator;

use Symplify\MonorepoBuilder\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\MonorepoBuilder\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\MonorepoBuilder\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\Kernel\MonorepoBuilderKernel;
use Symplify\MonorepoBuilder\Merge\ComposerJsonDecorator\RepositoryPathComposerJsonDecorator;
use Symplify\MonorepoBuilder\Tests\Merge\ComposerJsonDecorator\AbstractComposerJsonDecoratorTest;

final class RepositoryPathComposerJsonDecoratorTest extends AbstractComposerJsonDecoratorTest
{
    /**
     * @var mixed[]
     */
    private const COMPOSER_JSON_DATA = [
        ComposerJsonSection::REPOSITORIES => [
            [
                'type' => 'artifact',
                'url' => 'path/to/directory/with/zips/',

            ],
            [
                'type' => 'path',
                'url' => '../../libs/*/',
            ],
            [
                'type' => 'path',
                'url' => 'libs/*/',
            ],
        ],
    ];

    private ComposerJson $composerJson;

    private ComposerJson $expectedComposerJson;

    private RepositoryPathComposerJsonDecorator $repositoryPathComposerJsonDecorator;

    protected function setUp(): void
    {
        $this->bootKernel(MonorepoBuilderKernel::class);

        $this->repositoryPathComposerJsonDecorator = $this->getService(RepositoryPathComposerJsonDecorator::class);
        $this->composerJson = $this->createMainComposerJson();
        $this->expectedComposerJson = $this->createExpectedComposerJson();
    }

    public function test(): void
    {
        $this->repositoryPathComposerJsonDecorator->decorate($this->composerJson);

        $this->assertComposerJsonEquals($this->expectedComposerJson, $this->composerJson);
    }

    private function createMainComposerJson(): ComposerJson
    {
        /** @var ComposerJsonFactory $composerJsonFactory */
        $composerJsonFactory = $this->getService(ComposerJsonFactory::class);

        return $composerJsonFactory->createFromArray(self::COMPOSER_JSON_DATA);
    }

    private function createExpectedComposerJson(): ComposerJson
    {
        /** @var ComposerJsonFactory $composerJsonFactory */
        $composerJsonFactory = $this->getService(ComposerJsonFactory::class);

        $expectedComposerJson = [
            ComposerJsonSection::REPOSITORIES => [
                [
                    'type' => 'artifact',
                    'url' => 'path/to/directory/with/zips/',
                ],
                [
                    'type' => 'path',
                    'url' => 'libs/*/',
                ],
            ],
        ];

        return $composerJsonFactory->createFromArray($expectedComposerJson);
    }
}
