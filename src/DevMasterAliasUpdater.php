<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder;

use Symplify\MonorepoBuilder\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\ValueObject\Option;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Symplify\MonorepoBuilder\Tests\DevMasterAliasUpdater\DevMasterAliasUpdaterTest
 */
final class DevMasterAliasUpdater
{
    /**
     * @var string
     */
    private const EXTRA = 'extra';

    /**
     * @var string
     */
    private const BRANCH_ALIAS = 'branch-alias';

    /**
     * @var string
     */
    private const COMPOSER_BRANCH_PREFIX = 'dev-';

    private string $branchAliasTarget;

    public function __construct(
        private JsonFileManager $jsonFileManager,
        ParameterProvider $parameterProvider
    ) {
        $this->branchAliasTarget = self::COMPOSER_BRANCH_PREFIX . $parameterProvider->provideStringParameter(
            Option::DEFAULT_BRANCH_NAME
        );
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    public function updateFileInfosWithAlias(array $fileInfos, string $alias): void
    {
        foreach ($fileInfos as $fileInfo) {
            $json = $this->jsonFileManager->loadFromFileInfo($fileInfo);
            if ($this->shouldSkip($json, $alias)) {
                continue;
            }

            $json[self::EXTRA][self::BRANCH_ALIAS][$this->branchAliasTarget] = $alias;

            $this->jsonFileManager->printJsonToFileInfo($json, $fileInfo);
        }
    }

    /**
     * @param mixed[] $json
     */
    private function shouldSkip(array $json, string $alias): bool
    {
        // update only when already present
        if (! isset($json[self::EXTRA][self::BRANCH_ALIAS][$this->branchAliasTarget])) {
            return true;
        }

        $currentAlias = $json[self::EXTRA][self::BRANCH_ALIAS][$this->branchAliasTarget];

        return $currentAlias === $alias;
    }
}
