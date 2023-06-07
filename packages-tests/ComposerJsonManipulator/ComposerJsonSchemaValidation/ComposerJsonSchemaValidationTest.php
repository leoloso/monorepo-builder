<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Tests\ComposerJsonManipulator\ComposerJsonSchemaValidation;

use Symplify\MonorepoBuilder\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\Kernel\MonorepoBuilderKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ComposerJsonSchemaValidationTest extends AbstractKernelTestCase
{
    private JsonFileManager $jsonFileManager;

    private SmartFileSystem $smartFileSystem;

    protected function setUp(): void
    {
        $this->bootKernel(MonorepoBuilderKernel::class);

        $this->jsonFileManager = $this->getService(JsonFileManager::class);
        $this->smartFileSystem = new SmartFileSystem();
    }

    public function testCheckEmptyKeysAreRemoved(): void
    {
        $sourceJsonPath = __DIR__ . '/Source/symfony-website_skeleton-composer.json';
        $targetJsonPath = sys_get_temp_dir() . '/composer_json_manipulator_test_schema_validation.json';

        $sourceJson = $this->jsonFileManager->loadFromFilePath($sourceJsonPath);
        $this->smartFileSystem->dumpFile($targetJsonPath, $this->jsonFileManager->encodeJsonToFileContent($sourceJson));

        $sourceJson = $this->jsonFileManager->loadFromFilePath($sourceJsonPath);
        $targetJson = $this->jsonFileManager->loadFromFilePath($targetJsonPath);

        /*
         * Check empty keys are present in "source" but not in "target"
         */
        $this->assertArrayHasKey(ComposerJsonSection::REQUIRE_DEV, $sourceJson);
        $this->assertArrayHasKey('auto-scripts', $sourceJson['scripts']);
        $this->assertArrayNotHasKey(ComposerJsonSection::REQUIRE_DEV, $targetJson);
        $this->assertArrayNotHasKey('auto-scripts', $targetJson['scripts']);
    }
}
