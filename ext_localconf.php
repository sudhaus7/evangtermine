<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use ArbkomEKvW\Evangtermine\Controller\EventcontainerController;
use ArbkomEKvW\Evangtermine\Backend\FieldControl\ToggleAllFilters;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use ArbkomEKvW\Evangtermine\Routing\Aspect\Prevent404ValueMapper;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Core\Core\Environment;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$extensionkey = 'evangtermine';

ExtensionUtility::configurePlugin(
    $extensionkey,
    'List',
    [
        EventcontainerController::class => 'list, show, genericinfo',
    ],
    // non-cacheable actions
    [
        EventcontainerController::class => 'list, show, genericinfo',
    ]
);

ExtensionUtility::configurePlugin(
    $extensionkey,
    'Detail',
    [
        EventcontainerController::class => 'show, genericinfo',
    ],
    // non-cacheable actions
    [
        EventcontainerController::class => 'show, genericinfo',
    ]
);

ExtensionUtility::configurePlugin(
    $extensionkey,
    'Teaser',
    [
        EventcontainerController::class => 'teaser, show, genericinfo',
    ],
    // non-cacheable actions
    [
        EventcontainerController::class => 'teaser, show, genericinfo',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1715678295] = [
    'nodeName' => 'toggleAllFilters',
    'priority' => 30,
    'class' => ToggleAllFilters::class,
];

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine'] = [];
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine']['backend']
        = Typo3DatabaseBackend::class;
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine']['frontend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine']['frontend']
        = VariableFrontend::class;
}

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list'] = [];
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list']['backend']
        = Typo3DatabaseBackend::class;
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list']['frontend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list']['frontend']
        = VariableFrontend::class;
}

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser'] = [];
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser']['backend']
        = Typo3DatabaseBackend::class;
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser']['frontend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser']['frontend']
        = VariableFrontend::class;
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['Prevent404ValueMapper']
    = Prevent404ValueMapper::class;
/*$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['RegionStaticValueMapper']
    = ArbkomEKvW\Evangtermine\Routing\Aspect\RegionStaticValueMapper::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PlaceStaticValueMapper']
    = ArbkomEKvW\Evangtermine\Routing\Aspect\PlaceStaticValueMapper::class;*/

$GLOBALS['TYPO3_CONF_VARS']['LOG']['ArbkomEKvW']['Evangtermine']['Command']['writerConfiguration'] = [
    // configuration for ERROR level log entries
    LogLevel::DEBUG => [
        // add a FileWriter
        FileWriter::class => [
            // configuration for the writer
            'logFile' => Environment::getVarPath() . '/log/evangtermine_command.log',
        ],
    ],
];
