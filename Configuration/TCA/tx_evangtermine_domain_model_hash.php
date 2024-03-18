<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'Hash (für den Termin-Import)',
        'label' => 'hash',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:evangtermine/Resources/Public/Icons/Extension.png',
        'searchFields' => '',
    ],
    'palettes' => [],
    'types' => [
        '1' => ['showitem' => 'hidden,day,month,year,hash'],
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'day' => [
            'exclude' => 1,
            'label' => 'Tag',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,int',
                'readOnly' => true,
            ],
        ],
        'month' => [
            'exclude' => 1,
            'label' => 'Monat',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,int',
                'readOnly' => true,
            ],
        ],
        'year' => [
            'exclude' => 1,
            'label' => 'Jahr',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'hash' => [
            'exclude' => 1,
            'label' => 'Hashwert',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
    ],
];
