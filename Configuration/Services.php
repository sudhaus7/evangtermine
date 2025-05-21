<?php

declare(strict_types=1);

use ArbkomEKvW\Evangtermine\Command\ImportEventsCommand;
use ArbkomEKvW\Evangtermine\Solr\IndexService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('ArbkomEKvW\Evangtermine\\', __DIR__ . '/../Classes/*')
        ->exclude([
        __DIR__ . '/../Classes/Domain/Model/*',
    ]);

    $services->set(ImportEventsCommand::class)
        ->tag('console.command', [
        'command' => 'evangtermine:importevents',
        'description' => '',
    ]);

    $dependencyOrderingService = GeneralUtility::makeInstance(DependencyOrderingService::class);
    $packageManager = GeneralUtility::makeInstance(PackageManager::class, $dependencyOrderingService);
    if ($packageManager->isPackageActive('solr')) {
        $services->set(IndexService::class)
            ->public();
    }
};
