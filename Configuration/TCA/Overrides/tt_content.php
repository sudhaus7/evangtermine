<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/*
 * This file is part of the TYPO3 project.
 *
 * @author Frank Berger <fberger@sudhaus7.de>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

ExtensionUtility::registerPlugin('evangtermine', 'List', 'Evangelische Termine - Liste');

ExtensionUtility::registerPlugin('evangtermine', 'Detail', 'Evangelische Termine - Detail');

ExtensionUtility::registerPlugin('evangtermine', 'Teaser', 'Evangelische Termine - Teaser');

$pluginSignature = 'evangtermine_list';
ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', $pluginSignature, 'after:subheader');
$GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['columnsOverrides']['pi_flexform']['config'] = [
    'ds' => [
        'default' => 'FILE:EXT:evangtermine/Configuration/Flexforms/flexform_ds.xml',
    ],
];

$pluginSignature = 'evangtermine_teaser';
ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', $pluginSignature, 'after:subheader');
$GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['columnsOverrides']['pi_flexform']['config'] = [
    'ds' => [
        'default' => 'FILE:EXT:evangtermine/Configuration/Flexforms/flexform_ds.xml',
    ],
];

$pluginSignature = 'evangtermine_detail';
ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', $pluginSignature, 'after:subheader');
$GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['columnsOverrides']['pi_flexform']['config'] = [
    'ds' => [
        'default' => 'FILE:EXT:evangtermine/Configuration/Flexforms/flexform_ds.xml',
    ],
];
