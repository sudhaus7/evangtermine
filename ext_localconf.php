<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$extensionkey = 'evangtermine';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    $extensionkey,
    'List',
    [
        ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'list, show, genericinfo',
    ],
    // non-cacheable actions
    [
        ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'list, show, genericinfo',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    $extensionkey,
    'Detail',
    [
        ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'show, genericinfo',
    ],
    // non-cacheable actions
    [
        ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'show, genericinfo',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    $extensionkey,
    'Teaser',
    [
        ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'teaser, show, genericinfo',
    ],
    // non-cacheable actions
    [
        ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'teaser, show, genericinfo',
    ]
);

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine'] = [];
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine']['backend']
        = \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class;
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine']['frontend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine']['frontend']
        = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
}

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list'] = [];
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list']['backend']
        = \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class;
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list']['frontend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_list']['frontend']
        = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
}

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser'] = [];
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser']['backend']
        = \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class;
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser']['frontend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_event_teaser']['frontend']
        = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
}

/*$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['RegionStaticValueMapper']
    = ArbkomEKvW\Evangtermine\Routing\Aspect\RegionStaticValueMapper::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PlaceStaticValueMapper']
    = ArbkomEKvW\Evangtermine\Routing\Aspect\PlaceStaticValueMapper::class;*/

$GLOBALS['TYPO3_CONF_VARS']['LOG']['ArbkomEKvW']['Evangtermine']['Command']['writerConfiguration'] = [
    // configuration for ERROR level log entries
    \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
        // add a FileWriter
        \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
            // configuration for the writer
            'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/evangtermine_command.log',
        ],
    ],
];
