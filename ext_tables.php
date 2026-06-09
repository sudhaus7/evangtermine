<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$extensionkey = 'evangtermine';

// Activate Flexforms
$pluginSignature = 'evangtermine_list';
ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    // Flexform configuration schema file
    'FILE:EXT:evangtermine/Configuration/Flexforms/flexform_ds.xml',
    $pluginSignature
);

$pluginSignature = 'evangtermine_teaser';
ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    // Flexform configuration schema file
    'FILE:EXT:evangtermine/Configuration/Flexforms/flexform_ds.xml',
    $pluginSignature
);
