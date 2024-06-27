<?php

$GLOBALS['SiteConfiguration']['site']['columns']['evangtermineTargetPage'] = [
    'label' => 'This is the t3://page?uid= link or UID of the page where this sites evangtermine plugin is located',
    'config' => [
        'type' => 'input',
        'default' => '',
        'eval' => 'trim',
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']
    .= ',--div--;Evangelische Termine,evangtermineTargetPage';
