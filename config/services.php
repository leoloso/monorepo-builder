<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\Command\BumpInterdependencyCommand;
use Symplify\MonorepoBuilder\Command\PackageAliasCommand;
use Symplify\MonorepoBuilder\Command\PackagesJsonCommand;
use Symplify\MonorepoBuilder\Command\ValidateCommand;
use Symplify\MonorepoBuilder\Init\Command\InitCommand;
use Symplify\MonorepoBuilder\Merge\Command\MergeCommand;
use Symplify\MonorepoBuilder\Propagate\Command\PropagateCommand;
use Symplify\MonorepoBuilder\Release\Command\ReleaseCommand;
use Symplify\MonorepoBuilder\Testing\Command\LocalizeComposerPathsCommand;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;
use Symplify\PackageBuilder\Yaml\ParametersMerger;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Symplify\MonorepoBuilder\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Exception', __DIR__ . '/../src/Kernel', __DIR__ . '/../src/ValueObject']);

    // console
    /**
     * leoloso/symplify fork customization!
     *
     * Enable to add more commands to the Application service,
     * by allowing the custom monorepo-builder.php to set the
     * Application first, and add its own commands.
     *
     * If Application has already been set, use it.
     * Otherwise, create the service.
     */
    try {
        $applicationService = $services->get(Application::class);
    } catch (ServiceNotFoundException) {
        $applicationService = $services->set(Application::class);
    }
    // $services->set(Application::class)
    $applicationService
        ->call('addCommands', [[
            service(BumpInterdependencyCommand::class),
            service(InitCommand::class),
            service(LocalizeComposerPathsCommand::class),
            service(MergeCommand::class),
            service(PackageAliasCommand::class),
            service(PackagesJsonCommand::class),
            service(PropagateCommand::class),
            service(ReleaseCommand::class),
            service(ValidateCommand::class),
        ]]);

    $services->set(PrivatesCaller::class);
    $services->set(ParametersMerger::class);
};
