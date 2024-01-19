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

namespace ArbkomEKvW\Evangtermine\Util;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2023 Christoph Roth <christoph.roth@ekvw.de>, Evangelische Kirche von Westfalen
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 */
class FieldMapping
{
    /**
     * @return array
     */
    public function getFields(): array
    {
        return [
            'mode' => 'MODE',
            'subtitle' => 'SUBTITLE',
            'datum1' => 'DATUM',
            'datum2' => 'DATUM2',
            'monthbar' => 'monthbar',
            'allday' => 'allDay',
            'event_id' => '_event_ID',
            'event_inputmask_id' => '_event_INPUTMASK_ID',
            'title' => '_event_TITLE',
            'categories' => '_event_EVENTTYPE',
            'people' => '_event_PEOPLE',
            'short_description' => '_event_SHORT_DESCRIPTION',
            'long_description' => '_event_LONG_DESCRIPTION',
            'link' => '_event_LINK',
            'event_kat' => '_event_KAT',
            'event_kat2' => '_event_KAT_2',
            'email' => '_event_EMAIL',
            'event_person_id' => '_event_PERSON_ID',
            'event_place_id' => '_event_PLACE_ID',
            'region' => '_event_REGION_ID',
            'event_subregion_id' => '_event_SUBREGION_ID',
            'event_region2_id' => '_event_REGION_2_ID',
            'event_region3_id' => '_event_REGION_3_ID',
            'event_profession_id' => '_event_PROFESSION_ID',
            'event_music_kat_id' => '_event_MUSIC_KAT_ID',
            'event_flag1' => '_event_FLAG1',
            'textline1' => '_event_TEXTLINE_1',
            'textline2' => '_event_TEXTLINE_2',
            'textline3' => '_event_TEXTLINE_3',
            'textline4' => '_event_TEXTLINE_4',
            'textline5' => '_event_TEXTLINE_5',
            'textline6' => '_event_TEXTLINE_6',
            'textline7' => '_event_TEXTLINE_7',
            'textline8' => '_event_TEXTLINE_8',
            'textbox1' => '_event_TEXTBOX_1',
            'textbox2' => '_event_TEXTBOX_2',
            'textbox3' => '_event_TEXTBOX_3',
            'event_number1' => '_event_NUMBER_1',
            'event_number2' => '_event_NUMBER_2',
            'event_number3' => '_event_NUMBER_3',
            'event_menue1' => '_event_MENUE_1',
            'event_menue2' => '_event_MENUE_2',
            'event_yesno1' => '_event_YESNO_1',
            'event_yesno2' => '_event_YESNO_2',
            'event_yesno3' => '_event_YESNO_3',
            'event_destination' => '_event_DESTINATION',
            'event_status' => '_event_STATUS',
            'feedback_id' => '_event_FEEDBACK_ID',
            'highlight' => '_event_HIGHLIGHT',
            'event_coursetype' => '_event_COURSETYPE',
            'event_care' => '_event_CARE',
            'event_kollekte' => '_event_KOLLEKTE',
            'event_statistik' => '_event_STATISTIK',
            'event_external_id' => '_event_EXTERNAL_ID',
            'event_access' => '_event_ACCESS',
            'event_lang' => '_event_LANG',
            'event_user_id' => '_event_USER_ID',
            'caption' => '_event_CAPTION',
            'event_modified' => '_event_MODIFIED',
            'event_koll_descr' => '_event_KOLL_DESCR',
            'poll_id' => '_poll_ID',
            'webform_linkname' => '_webform_LINKNAME',
            'inputmask_name' => '_inputmask_NAME',
            'place_id' => '_place_ID',
            'place_name' => '_place_NAME',
            'place_street_nr' => '_place_STREET_NR',
            'place_zip' => '_place_ZIP',
            'place_city' => '_place_CITY',
            'place_info' => '_place_INFO',
            'place_hidden' => '_place_HIDDEN',
            'place_image_caption' => '_place_CAPTION',
            'place_position' => '_place_POSITION',
            'place_kat' => '_place_KAT',
            'place_open' => '_place_OPEN',
            'place_equip' => '_place_EQUIP',
            'place_equiptext' => '_place_EQUIPTEXT',
            'place_region' => '_place_REGION',
            'lat' => '_place_GLAT',
            'lon' => '_place_GLONG',
            'person_name' => '_person_NAME',
            'person_email' => '_person_EMAIL',
            'person_contact' => '_person_CONTACT',
            'person_position' => '_person_POSITION',
            'person_surname' => '_person_SURNAME',
            'user_id' => '_user_ID',
            'user_realname' => '_user_REALNAME',
            'user_description' => '_user_DESCRIPTION',
            'user_street_nr' => '_user_STREET_NR',
            'user_zip' => '_user_ZIP',
            'user_city' => '_user_CITY',
            'user_email' => '_user_EMAIL',
            'user_url' => '_user_URL',
            'user_contact' => '_user_CONTACT',
            'user_intdata' => '_user_INTDATA',
            'liturg_bez' => 'LITURG_BEZ',
            'channels' => 'CHANNELS',
        ];
    }
}
