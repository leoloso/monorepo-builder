<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Command;

use Nette\Utils\Json;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\MonorepoBuilder\Json\PackageJsonProvider;
use Symplify\MonorepoBuilder\ValueObject\Option;
use Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class PackagesJsonCommand extends AbstractSymplifyCommand
{
    public function __construct(
        private PackageJsonProvider $packageJsonProvider
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Provides package paths in json format. Useful for GitHub Actions Workflow');
        $this->addOption(Option::TESTS, null, InputOption::VALUE_NONE, 'Only with /tests directory');
        $this->addOption(
            Option::EXCLUDE_PACKAGE,
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'Exclude one or more package from the list, useful e.g. when scoping one package instead of bare split'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $onlyTests = (bool) $input->getOption(Option::TESTS);
        if ($onlyTests) {
            $packagePaths = $this->packageJsonProvider->providePackagesWithTests();
        } else {
            $packagePaths = $this->packageJsonProvider->providePackages();
        }

        $excludedPackages = (array) $input->getOption(Option::EXCLUDE_PACKAGE);
        $allowedPackagePaths = array_diff($packagePaths, $excludedPackages);

        // re-index from 0
        $allowedPackagePaths = array_values($allowedPackagePaths);

        // must be without spaces, otherwise it breaks GitHub Actions json
        $json = Json::encode($allowedPackagePaths);
        echo $json;

        return self::SUCCESS;
    }
}
