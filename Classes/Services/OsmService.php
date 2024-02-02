<?php

namespace ArbkomEKvW\Evangtermine\Services;

class OsmService
{
    /**
     * @param string $address
     * @return array
     */
    public function determineCoordinates(string $address): array
    {
        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($address) . '&format=json&addressdetails=1';

        $ch = curl_init();
        $userAgent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/22.0.1216.0 Safari/537.2';
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.example.com/1');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return [];
        }
        curl_close($ch);

        if (empty($responseData)) {
            return ['0', '0'];
        }
        $responseDataArray = json_decode($responseData);
        $lat = $responseDataArray[0]->lat ?? '0';
        $lon = $responseDataArray[0]->lon ?? '0';
        return [$lat, $lon];
    }
}
