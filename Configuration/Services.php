<?php

declare(strict_types=1);

use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use ArbkomEKvW\Evangtermine\Services\Events\Imported\EventsService;
use ArbkomEKvW\Evangtermine\Command\ImportEventsCommand;
use ArbkomEKvW\Evangtermine\Services\Events\EventsServiceInterface;
use ArbkomEKvW\Evangtermine\Solr\DummyIndexService;
use ArbkomEKvW\Evangtermine\Solr\IndexService;
use ArbkomEKvW\Evangtermine\Solr\IndexServiceInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    if (class_exists(Queue::class)) {
        $services->load('ArbkomEKvW\Evangtermine\\', __DIR__ . '/../Classes/*')
            ->exclude([
                __DIR__ . '/../Classes/Domain/Model/*',
            ]);
        $services->alias(
            IndexServiceInterface::class,
            IndexService::class
        );
    } else {
        $services->load('ArbkomEKvW\Evangtermine\\', __DIR__ . '/../Classes/*')
            ->exclude([
                __DIR__ . '/../Classes/Domain/Model/*',
                __DIR__ . '/../Classes/Solr/EventStrategy.php',
                __DIR__ . '/../Classes/Solr/IndexService.php',
                __DIR__ . '/../Classes/Solr/TermineInitializer.php',
            ]);
        $services->alias(
            IndexServiceInterface::class,
            DummyIndexService::class
        );
    }

    $services->set(ImportEventsCommand::class)
        ->tag('console.command', [
            'command' => 'evangtermine:importevents',
            'description' => '',
        ]);

    $extConfig  = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('evangtermine');
    if (!empty($extConfig['importEvents'])) {
        $services->alias(
            EventsServiceInterface::class,
            EventsService::class
        );
    } else {
        $services->alias(
            EventsServiceInterface::class,
            \ArbkomEKvW\Evangtermine\Services\Events\Api\EventsService::class
        );
    }
};
