<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

$extensionkey = 'evangtermine';

// Activate Flexforms
$pluginSignature = 'evangtermine_list';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    // Flexform configuration schema file
    'FILE:EXT:evangtermine/Configuration/Flexforms/flexform_ds.xml'
);

$pluginSignature = 'evangtermine_teaser';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    // Flexform configuration schema file
    'FILE:EXT:evangtermine/Configuration/Flexforms/flexform_ds.xml'
);
