<?php

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

$EM_CONF[$_EXTKEY] = [
    'title' => 'Evangelische Termine',
    'description' => 'Anzeige des Terminkalenders der Vernetzten Kirche in der Ev.-Luth. Kirche in Bayern und weiteren teilnehmenden evangelischen Landeskirchen.',
    'category' => 'plugin',
    'author' => 'Frank Berger',
    'author_email' => 'fberger@sudhaus7.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '2.2.7',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
              'ArbkomEKvW\\Evangtermine\\' => 'Classes',
        ],
    ],
];
