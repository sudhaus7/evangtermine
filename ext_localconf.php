<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


$extensionkey = 'evangtermine';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	$extensionkey,
	'List',
	array(
		ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'list, show, genericinfo'
	),
	// non-cacheable actions
	array(
		ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'list, show, genericinfo'
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	$extensionkey,
	'Detail',
	array(
		ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'show, genericinfo'
	),
	// non-cacheable actions
	array(
		ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'show, genericinfo'
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	$extensionkey,
	'Teaser',
	array(
		ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'teaser, show, genericinfo'
	),
	// non-cacheable actions
	array(
		ArbkomEKvW\Evangtermine\Controller\EventcontainerController::class => 'teaser, show, genericinfo'
	)
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['EventStaticValueMapper']
    = ArbkomEKvW\Evangtermine\Routing\Aspect\EventStaticValueMapper::class;

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_eventstaticvaluemapper'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_eventstaticvaluemapper'] = [];
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_eventstaticvaluemapper']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_eventstaticvaluemapper']['backend']
        = \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class;
}
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_eventstaticvaluemapper']['frontend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['evangtermine_eventstaticvaluemapper']['frontend']
        = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
}
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
