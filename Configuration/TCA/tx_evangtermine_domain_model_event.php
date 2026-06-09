<?php

declare(strict_types=1);

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
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:evangtermine/Resources/Public/Icons/Extension.png',
    ],
    'palettes' => [
        'general' => [
            'showitem' => 'hidden,--linebreak--,id,--linebreak--,hash,--linebreak--,title,--linebreak--,subtitle,--linebreak--,liturg_bez,--linebreak--,email,link,--linebreak--,short_description,--linebreak--,long_description,--linebreak--,image,caption,--linebreak--,highlight,--linebreak--,people,--linebreak--,categories,--linebreak--feedback_id,--linebreak--,poll_id,webform_linkname,--linebreak--,event_modified,--linebreak--,channels,--linebreak--,resources,--linebreak--,slug',
        ],
        'date' => [
            'showitem' => 'start,end,--linebreak--,mode,--linebreak--,datum1,datum2,--linebreak--,monthbar,allday',
        ],
        'place' => [
            'showitem' => 'place_id,--linebreak--,place_name,--linebreak--,place_street_nr,--linebreak--,place_zip,place_city,--linebreak--,region,--linebreak--,place_image,--linebreak--,place_image_caption,--linebreak--,place_info,place_hidden,--linebreak--,place_position,place_kat,--linebreak--,place_open,--linebreak--,place_equip,place_equiptext,--linebreak--,place_region',
        ],
        'event' => [
            'showitem' => 'event_id,event_inputmask_id,--linebreak--,event_kat,event_kat2,--linebreak--,event_person_id,event_place_id,--linebreak--,event_subregion_id,--linebreak--,event_region2_id,event_region3_id,--linebreak--,event_profession_id,event_music_kat_id,--linebreak--,event_flag1,--linebreak--,event_number1,event_number2,event_number3,--linebreak--,event_menue1,event_menue2,--linebreak--,event_yesno1,event_yesno2,event_yesno3,--linebreak--,event_destination,event_status,--linebreak--,event_coursetype,event_care,--linebreak--,event_kollekte,event_statistik,--linebreak--,event_external_id,event_access,--linebreak--,event_lang,event_user_id,--linebreak--,event_koll_descr,--linebreak--,inputmask_name',
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
        'attributes' => [
            'showitem' => 'attributes,output_order',
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
                --div--;Attribute,
                --palette--;;attributes,
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
                        'label' => '',
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
                'searchable' => false,
            ],
        ],
        'hash' => [
            'exclude' => 1,
            'label' => 'Hash',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'start' => [
            'exclude' => 1,
            'label' => 'Datum (Beginn)',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'end' => [
            'exclude' => 1,
            'label' => 'Datum (Ende)',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'mode' => [
            'exclude' => 1,
            'label' => 'Start-End-Modus',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'subtitle' => [
            'exclude' => 1,
            'label' => 'Untertitel',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'datum1' => [
            'exclude' => 1,
            'label' => 'Datum 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'datum2' => [
            'exclude' => 1,
            'label' => 'Datum 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'monthbar' => [
            'exclude' => 1,
            'label' => 'Monthbar',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'allday' => [
            'exclude' => 1,
            'label' => 'Die Veranstaltung dauert den ganzen Tag',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
            ],
        ],
        'event_id' => [
            'exclude' => 1,
            'label' => 'Veranstaltungs-ID',
            'config' => [
                'type' => 'number',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'event_inputmask_id' => [
            'exclude' => 1,
            'label' => 'Eingabformular-ID',
            'config' => [
                'type' => 'number',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'title' => [
            'exclude' => 1,
            'label' => 'Titel',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'categories' => [
            'exclude' => 1,
            'label' => 'Kategorien',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'people' => [
            'exclude' => 1,
            'label' => 'Zielgruppen',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'short_description' => [
            'exclude' => 1,
            'label' => 'Kurzbeschreibung',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
                'searchable' => false,
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
                'searchable' => false,
            ],
        ],
        'link' => [
            'exclude' => 1,
            'label' => 'Link',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_kat' => [
            'exclude' => 1,
            'label' => 'Veranstaltungskategorie',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_kat2' => [
            'exclude' => 1,
            'label' => 'Veranstaltungskategorie 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'E-Mail',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_person_id' => [
            'exclude' => 1,
            'label' => 'Ansprechpartner-Id',
            'config' => [
                'type' => 'number',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'event_place_id' => [
            'exclude' => 1,
            'label' => 'Kirchen-Id',
            'config' => [
                'type' => 'number',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'region' => [
            'exclude' => 1,
            'label' => 'Region',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_subregion_id' => [
            'exclude' => 1,
            'label' => 'Dekanatsbezirk',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_region2_id' => [
            'exclude' => 1,
            'label' => 'Region 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_region3_id' => [
            'exclude' => 1,
            'label' => 'Region 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_profession_id' => [
            'exclude' => 1,
            'label' => 'Beruf',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_music_kat_id' => [
            'exclude' => 1,
            'label' => 'Musikalische Kategorie',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_flag1' => [
            'exclude' => 1,
            'label' => 'Ausgabe auf www.solideo.de',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textline1' => [
            'exclude' => 1,
            'label' => 'Textline 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textline2' => [
            'exclude' => 1,
            'label' => 'Textline 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textline3' => [
            'exclude' => 1,
            'label' => 'Textline 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textline4' => [
            'exclude' => 1,
            'label' => 'Textline 4',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textline5' => [
            'exclude' => 1,
            'label' => 'Textline 5',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textline6' => [
            'exclude' => 1,
            'label' => 'Textline 6',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textline7' => [
            'exclude' => 1,
            'label' => 'Textline 7',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textline8' => [
            'exclude' => 1,
            'label' => 'Textline 8',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textbox1' => [
            'exclude' => 1,
            'label' => 'Textbox 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textbox2' => [
            'exclude' => 1,
            'label' => 'Textbox 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'textbox3' => [
            'exclude' => 1,
            'label' => 'Textbox 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_number1' => [
            'exclude' => 1,
            'label' => 'Veranstaltungsnummer 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_number2' => [
            'exclude' => 1,
            'label' => 'Veranstaltungsnummer 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_number3' => [
            'exclude' => 1,
            'label' => 'Veranstaltungsnummer 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_menue1' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Menü 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_menue2' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Menü 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_yesno1' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Ja/Nein 1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_yesno2' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Ja/Nein 2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_yesno3' => [
            'exclude' => 1,
            'label' => 'Veranstaltung - Ja/Nein 3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_destination' => [
            'exclude' => 1,
            'label' => 'Öffentlich oder Intern',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_status' => [
            'exclude' => 1,
            'label' => 'Freigabe',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'feedback_id' => [
            'exclude' => 1,
            'label' => 'Feedback-Id',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'highlight' => [
            'exclude' => 1,
            'label' => 'Highlight',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'nichts ausgewählt', 'value' => 0],
                    ['label' => 'kein Highlight', 'value' => 1],
                    ['label' => 'Highlight', 'value' => 2],
                    ['label' => 'regionales Highlight', 'value' => 3],
                ],
                'readOnly' => 1,
            ],
        ],
        'event_coursetype' => [
            'exclude' => 1,
            'label' => 'Kurstyp',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'event_care' => [
            'exclude' => 1,
            'label' => 'Themen',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'event_kollekte' => [
            'exclude' => 1,
            'label' => 'Kollekte',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'event_statistik' => [
            'exclude' => 1,
            'label' => 'Statistische Angaben / Teilnehmerzahl',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'event_external_id' => [
            'exclude' => 1,
            'label' => 'Externe ID',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'event_access' => [
            'exclude' => 1,
            'label' => 'Angaben zur Barrierefreiheit',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'event_lang' => [
            'exclude' => 1,
            'label' => 'Sprache(n)',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'event_user_id' => [
            'exclude' => 1,
            'label' => 'Veranstalter-ID',
            'config' => [
                'type' => 'number',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
            ],
        ],
        'image' => [
            'exclude' => 1,
            'label' => 'Bild der Veranstaltung',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'caption' => [
            'exclude' => 1,
            'label' => 'Bildunterschrift',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_modified' => [
            'exclude' => 1,
            'label' => 'Zuletzt geändert',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'event_koll_descr' => [
            'exclude' => 1,
            'label' => 'Kurzbeschreibung der Kollekte',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'poll_id' => [
            'exclude' => 1,
            'label' => 'Poll-Id',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'webform_linkname' => [
            'exclude' => 1,
            'label' => 'Webformular-Link',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'inputmask_name' => [
            'exclude' => 1,
            'label' => 'Eingabformular-Name',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'place_id' => [
            'exclude' => 1,
            'label' => 'Ort - Id',
            'config' => [
                'type' => 'number',
                'eval' => 'trim',
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
                'searchable' => false,
            ],
        ],
        'place_street_nr' => [
            'exclude' => 1,
            'label' => 'Ort - Straße/Hausnummer',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'place_zip' => [
            'exclude' => 1,
            'label' => 'Ort - PLZ',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'place_city' => [
            'exclude' => 1,
            'label' => 'Ort - Stadt/Ort',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
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
                'searchable' => false,
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
                'searchable' => false,
            ],
        ],
        'place_image' => [
            'exclude' => 1,
            'label' => 'Ort - Bild',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'place_image_caption' => [
            'exclude' => 1,
            'label' => 'Ort - Bildunterschrift',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'place_position' => [
            'exclude' => 1,
            'label' => 'Ort - Position',
            'config' => [
                'type' => 'number',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'place_kat' => [
            'exclude' => 1,
            'label' => 'Ort - Kategorie',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'place_open' => [
            'exclude' => 1,
            'label' => 'Ort - Öffnungszeiten',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'place_equip' => [
            'exclude' => 1,
            'label' => 'Angaben zur Barrierefreiheit',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'place_equiptext' => [
            'exclude' => 1,
            'label' => 'Weitere Angaben zur Barrierefreiheit',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'place_region' => [
            'exclude' => 1,
            'label' => 'Ort - Region',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'lat' => [
            'exclude' => 1,
            'label' => 'Latitude',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'lon' => [
            'exclude' => 1,
            'label' => 'Longitude',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'person_name' => [
            'exclude' => 1,
            'label' => 'Person - Name',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'person_email' => [
            'exclude' => 1,
            'label' => 'Person - E-Mail',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'person_contact' => [
            'exclude' => 1,
            'label' => 'Person - Kontaktdaten',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'person_position' => [
            'exclude' => 1,
            'label' => 'Person - Position',
            'config' => [
                'type' => 'number',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'person_surname' => [
            'exclude' => 1,
            'label' => 'Person - Nachname',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_id' => [
            'exclude' => 1,
            'label' => 'Benutzer - Id',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_realname' => [
            'exclude' => 1,
            'label' => 'Benutzer - Name',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_description' => [
            'exclude' => 1,
            'label' => 'Benutzer - Beschreibung',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_street_nr' => [
            'exclude' => 1,
            'label' => 'Benutzer - Straße/Hausnummer',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_zip' => [
            'exclude' => 1,
            'label' => 'Benutzer - PLZ',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_city' => [
            'exclude' => 1,
            'label' => 'Benutzer - Stadt/Ort',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_email' => [
            'exclude' => 1,
            'label' => 'Benutzer - E-Mail',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_url' => [
            'exclude' => 1,
            'label' => 'Benutzer - URL',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_contact' => [
            'exclude' => 1,
            'label' => 'Benutzer - Kontaktdaten',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_intdata' => [
            'exclude' => 1,
            'label' => 'Veranstalter Zusatzdaten',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'user_image' => [
            'exclude' => 1,
            'label' => 'Benutzer - Bild',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => 1,
                'searchable' => false,
            ],
        ],
        'liturg_bez' => [
            'exclude' => 1,
            'label' => 'Liturgische Bezeichnung',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'channels' => [
            'exclude' => 1,
            'label' => 'Kanäle',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'resources' => [
            'exclude' => 1,
            'label' => 'Ressourcen',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'attributes' => [
            'label' => 'Attribute',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
                'searchable' => false,
            ],
        ],
        'output_order' => [
            'label' => 'Ausgabereihenfolge',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
                'searchable' => false,
            ],
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
                'searchable' => false,
            ],
        ],
    ],
];
