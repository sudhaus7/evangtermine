<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'Termin',
        'label' => 'title',
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
        'searchFields' => 'title',
    ],
    'palettes' => [
        'general' => [
            'showitem' => 'hidden,--linebreak--,id,--linebreak--,title,--linebreak--,subtitle,--linebreak--,liturg_bez,--linebreak--,email,link,--linebreak--,short_description,--linebreak--,long_description,--linebreak--,image,caption,--linebreak--,highlight,--linebreak--,people,--linebreak--,categories,--linebreak--feedback_id,--linebreak--,poll_id,webform_linkname,--linebreak--,event_modified,--linebreak--,channels,--linebreak--,slug'
        ],
        'date' => [
            'showitem' => 'start,end,--linebreak--,mode,--linebreak--,datum1,datum2,--linebreak--,monthbar,allday',
        ],
        'place' => [
            'showitem' => 'place_id,--linebreak--,place_name,--linebreak--,place_street_nr,--linebreak--,place_zip,place_city,--linebreak--,region,--linebreak--,place_image,--linebreak--,place_image_caption,--linebreak--,place_info,place_hidden,--linebreak--,place_position,place_kat,--linebreak--,place_open,--linebreak--,place_equip,place_equiptext,--linebreak--,place_region',
        ],
        'event' => [
            'showitem' => 'event_id,event_inputmask_id,--linebreak--,event_kat,event_kat2,--linebreak--,event_person_id,event_place_id,--linebreak--,event_subregion_id,--linebreak--,event_region2_id,event_region3_id,--linebreak--,event_profession_id,event_music_kat_id,--linebreak--,event_flag1,--linebreak--,event_number1,event_number2,event_number3,--linebreak--,event_menue1,event_menue2,--linebreak--,event_yesno1,event_yesno2,event_yesno3,--linebreak--,event_destination,event_status,--linebreak--,event_coursetype,event_care,--linebreak--,event_kollekte,event_statistik,--linebreak--,event_external_id,event_access,--linebreak--,event_lang,event_user_id,--linebreak--,event_koll_descr,--linebreak--,inputmask_name'
        ],
        'person' => [
            'showitem' => 'person_name,person_surname,--linebreak--,person_email,--linebreak--,person_contact,person_position',
        ],
        'user' => [
            'showitem' => 'user_id,--linebreak--,user_realname,--linebreak--,user_street_nr,--linebreak--,user_zip,user_city,--linebreak--,user_email,user_url,--linebreak--,user_contact,--linebreak--,user_description,--linebreak--,user_image,--linebreak--,user_intdata',
        ],
        'geo' => [
            'showitem' => 'lat,lon',
        ],
        'text' => [
            'showitem' => 'textbox1,textbox2,--linebreak--,textbox3,--linebreak--,textline1,textline2,--linebreak--,textline3,textline4,--linebreak--,textline5,textline6,--linebreak--,textline7,textline8',
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => ',
                --div--;Allgemein,
                --palette--;;general,
                --div--;Datum,
                --palette--;;date,
                --div--;Ort,
                --palette--;;place,
                --div--;Veranstaltung,
                --palette--;;event,
                --div--;Person,
                --palette--;;person,
                --div--;Benutzer,
                --palette--;;user,
                --div--;Texte,
                --palette--;;text,
                --div--;Geokoordinaten,
                --palette--;;geo,
                ',
        ],
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
        'id' => [
            'exclude' => 1,
            'label' => 'Id',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'start' => [
            'exclude' => 1,
            'label' => 'Datum (Beginn)',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => true,
            ]
        ],
        'end' => [
            'exclude' => 1,
            'label' => 'Datum (Ende)',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => true,
            ]
        ],
        'mode' => [
            'exclude' => 1,
            'label' => 'Start-End-Modus',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'subtitle' => [
            'exclude' => 1,
            'label' => 'Untertitel',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'datum1' => [
            'exclude' => 1,
            'label' => 'Datum 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'datum2' => [
            'exclude' => 1,
            'label' => 'Datum 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'monthbar' => [
            'exclude' => 1,
            'label' => 'Monthbar',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'allday' => [
            'exclude' => 1,
            'label' => 'Die Veranstaltung dauert den ganzen Tag',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
            ]
        ],
        'event_id' => [
            'exclude' => 1,
            'label' => 'Veranstaltungs-ID',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,int',
                'readOnly' => true,
            ]
        ],
        'event_inputmask_id' => [
            'exclude' => 1,
            'label' => 'Eingabformular-ID',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,int',
                'readOnly' => true,
            ]
        ],
        'title' => [
            'exclude' => 1,
            'label' => 'Titel',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'categories' => [
            'exclude' => 1,
            'label' => 'Kategorien',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'people' => [
            'exclude' => 1,
            'label' => 'Zielgruppen',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'short_description' => [
            'exclude' => 1,
            'label' => 'Kurzbeschreibung',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
            ],
        ],
        'long_description' => [
            'exclude' => 1,
            'label' => 'Beschreibung',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
            ],
        ],
        'link' => [
            'exclude' => 1,
            'label' => 'Link',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_kat' => [
            'exclude' => 1,
            'label' => 'Veranstaltungskategorie',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_kat2' => [
            'exclude' => 1,
            'label' => 'Veranstaltungskategorie 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'E-Mail',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_person_id' => [
            'exclude' => 1,
            'label' => 'Ansprechpartner-Id',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,int',
                'readOnly' => true,
            ]
        ],
        'event_place_id' => [
            'exclude' => 1,
            'label' => 'Kirchen-Id',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,int',
                'readOnly' => true,
            ]
        ],
        'region' => [
            'exclude' => 1,
            'label' => 'Region',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'event_subregion_id' => [
            'exclude' => 1,
            'label' => 'Dekanatsbezirk',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'event_region2_id' => [
            'exclude' => 1,
            'label' => 'Region 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'event_region3_id' => [
            'exclude' => 1,
            'label' => 'Region 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'event_profession_id' => [
            'exclude' => 1,
            'label' => 'Beruf',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'event_music_kat_id' => [
            'exclude' => 1,
            'label' => 'Musikalische Kategorie',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'event_flag1' => [
            'exclude' => 1,
            'label' => 'Ausgabe auf www.solideo.de',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'textline1' => [
            'exclude' => 1,
            'label' => 'Textline 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textline2' => [
            'exclude' => 1,
            'label' => 'Textline 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textline3' => [
            'exclude' => 1,
            'label' => 'Textline 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textline4' => [
            'exclude' => 1,
            'label' => 'Textline 4',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textline5' => [
            'exclude' => 1,
            'label' => 'Textline 5',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textline6' => [
            'exclude' => 1,
            'label' => 'Textline 6',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textline7' => [
            'exclude' => 1,
            'label' => 'Textline 7',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textline8' => [
            'exclude' => 1,
            'label' => 'Textline 8',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textbox1' => [
            'exclude' => 1,
            'label' => 'Textbox 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textbox2' => [
            'exclude' => 1,
            'label' => 'Textbox 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'textbox3' => [
            'exclude' => 1,
            'label' => 'Textbox 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_number1' => [
            'exclude' => 1,
            'label' => 'Veranstaltungsnummer 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_number2' => [
            'exclude' => 1,
            'label' => 'Veranstaltungsnummer 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_number3' => [
            'exclude' => 1,
            'label' => 'Veranstaltungsnummer 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_menue1' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Menü 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_menue2' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Menü 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_yesno1' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Ja/Nein 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_yesno2' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Ja/Nein 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_yesno3' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Ja/Nein 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_destination' => [
            'exclude' => 1,
            'label' => 'Öffentlich oder Intern',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_status' => [
            'exclude' => 1,
            'label' => 'Freigabe',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'feedback_id' => [
            'exclude' => 1,
            'label' => 'Feedback-Id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'highlight' => [
            'exclude' => 1,
            'label' => 'Highlight',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['nichts ausgewählt', 0],
                    ['kein Highlight', 1],
                    ['Highlight', 2],
                    ['regionales Highlight', 3],
                ],
                'readOnly' => 1,
            ]
        ],
        'event_coursetype' => [
            'exclude' => 1,
            'label' => 'Kurstyp',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'event_care' => [
            'exclude' => 1,
            'label' => 'Themen',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'event_kollekte' => [
            'exclude' => 1,
            'label' => 'Kollekte',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'event_statistik' => [
            'exclude' => 1,
            'label' => 'Statistische Angaben / Teilnehmerzahl',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'event_external_id' => [
            'exclude' => 1,
            'label' => 'Externe ID',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'event_access' => [
            'exclude' => 1,
            'label' => 'Angaben zur Barrierefreiheit',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'event_lang' => [
            'exclude' => 1,
            'label' => 'Sprache(n)',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'event_user_id' => [
            'exclude' => 1,
            'label' => 'Veranstalter-ID',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,int',
                'readOnly' => 1,
            ]
        ],
        'image' => [
            'exclude' => 1,
            'label' => 'Bild der Veranstaltung',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'maxitems' => 1,
                    'minitems' => 0,
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:fwd_template/Resources/Private/Language/locallang_db.xlf:tx_fwd_template.addImage'
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '1' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '2' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '3' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '4' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '5' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                        ]
                    ]
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ],
        'caption' => [
            'exclude' => 1,
            'label' => 'Bildunterschrift',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_modified' => [
            'exclude' => 1,
            'label' => 'Zuletzt geändert',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'event_koll_descr' => [
            'exclude' => 1,
            'label' => 'Kurzbeschreibung der Kollekte',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
            ]
        ],
        'poll_id' => [
            'exclude' => 1,
            'label' => 'Poll-Id',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'webform_linkname' => [
            'exclude' => 1,
            'label' => 'Webformular-Link',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'inputmask_name' => [
            'exclude' => 1,
            'label' => 'Eingabformular-Name',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'place_id' => [
            'exclude' => 1,
            'label' => 'Ort - Id',
            'config' => [
                'type' => 'input',
                'eval' => 'int,trim',
                'readOnly' => true,
            ],
        ],
        'place_name' => [
            'exclude' => 1,
            'label' => 'Ort - Bezeichnung',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'place_street_nr' => [
            'exclude' => 1,
            'label' => 'Ort - Straße/Hausnummer',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'place_zip' => [
            'exclude' => 1,
            'label' => 'Ort - PLZ',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'place_city' => [
            'exclude' => 1,
            'label' => 'Ort - Stadt/Ort',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'place_info' => [
            'exclude' => 1,
            'label' => 'Ort - Informationen',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
            ],
        ],
        'place_hidden' => [
            'exclude' => 1,
            'label' => 'Verwaltungsinformationen',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
            ],
        ],
        'place_image' => [
            'exclude' => 1,
            'label' => 'Ort - Bild',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'place_image',
                [
                    'maxitems' => 1,
                    'minitems' => 0,
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:fwd_template/Resources/Private/Language/locallang_db.xlf:tx_fwd_template.addImage'
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '1' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '2' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '3' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '4' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '5' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                        ]
                    ]
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ],
        'place_image_caption' => [
            'exclude' => 1,
            'label' => 'Ort - Bildunterschrift',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'place_position' => [
            'exclude' => 1,
            'label' => 'Ort - Position',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,int',
                'readOnly' => true,
            ]
        ],
        'place_kat' => [
            'exclude' => 1,
            'label' => 'Ort - Kategorie',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'place_open' => [
            'exclude' => 1,
            'label' => 'Ort - Öffnungszeiten',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
            ]
        ],
        'place_equip' => [
            'exclude' => 1,
            'label' => 'Angaben zur Barrierefreiheit',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'place_equiptext' => [
            'exclude' => 1,
            'label' => 'Weitere Angaben zur Barrierefreiheit',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'place_region' => [
            'exclude' => 1,
            'label' => 'Ort - Region',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'lat' => [
            'exclude' => 1,
            'label' => 'Latitude',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'lon' => [
            'exclude' => 1,
            'label' => 'Longitude',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ]
        ],
        'person_name' => [
            'exclude' => 1,
            'label' => 'Person - Name',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'person_email' => [
            'exclude' => 1,
            'label' => 'Person - E-Mail',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'person_contact' => [
            'exclude' => 1,
            'label' => 'Person - Kontaktdaten',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'person_position' => [
            'exclude' => 1,
            'label' => 'Person - Position',
            'config' => [
                'type' => 'input',
                'eval' => 'trim,int',
                'readOnly' => true,
            ]
        ],
        'person_surname' => [
            'exclude' => 1,
            'label' => 'Person - Nachname',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_id' => [
            'exclude' => 1,
            'label' => 'Benutzer - Id',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_realname' => [
            'exclude' => 1,
            'label' => 'Benutzer - Name',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_description' => [
            'exclude' => 1,
            'label' => 'Benutzer - Beschreibung',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_street_nr' => [
            'exclude' => 1,
            'label' => 'Benutzer - Straße/Hausnummer',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_zip' => [
            'exclude' => 1,
            'label' => 'Benutzer - PLZ',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_city' => [
            'exclude' => 1,
            'label' => 'Benutzer - Stadt/Ort',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_email' => [
            'exclude' => 1,
            'label' => 'Benutzer - E-Mail',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_url' => [
            'exclude' => 1,
            'label' => 'Benutzer - URL',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_contact' => [
            'exclude' => 1,
            'label' => 'Benutzer - Kontaktdaten',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_intdata' => [
            'exclude' => 1,
            'label' => 'Veranstalter Zusatzdaten',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'user_image' => [
            'exclude' => 1,
            'label' => 'Benutzer - Bild',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'user_image',
                [
                    'maxitems' => 1,
                    'minitems' => 0,
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:fwd_template/Resources/Private/Language/locallang_db.xlf:tx_fwd_template.addImage'
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '1' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '2' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '3' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '4' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            '5' => [
                                'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                        ]
                    ]
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ],
        'liturg_bez' => [
            'exclude' => 1,
            'label' => 'Liturgische Bezeichnung',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'channels' => [
            'exclude' => 1,
            'label' => 'Kanäle',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ]
        ],
        'slug' => [
            'label' => 'Slug',
            'exclude' => 1,
            'config' => [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['title'],
                    'fieldSeparator' => '-',
                    'prefixParentPageSlug' => false,
                    'replacements' => [
                        '/' => '-',
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'unique',
                'prependSlash' => true,
            ],
        ],
    ],
];
