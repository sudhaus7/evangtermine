<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\StaticValueMapper;

abstract class AbstractStaticValueMapper extends StaticValueMapper
{
    protected function changeString(string $string): array|string
    {
        $string = str_replace(' ', '-', $string);
        $string = str_replace('--', '', $string);
        $string = str_replace('ß', 'ss', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace("'", '', $string);
        $string = str_replace('.', '', $string);
        $string = str_replace(':', '', $string);
        $string = str_replace('?', '', $string);
        $string = str_replace('!', '', $string);
        $string = str_replace('(', '', $string);
        $string = str_replace(')', '', $string);
        $string = mb_strtolower($string);
        $string = str_replace('ä', 'ae', $string);
        $string = str_replace('ö', 'oe', $string);
        $string = str_replace('ü', 'ue', $string);
        return str_replace("\r\n", '', $string);
    }
}
