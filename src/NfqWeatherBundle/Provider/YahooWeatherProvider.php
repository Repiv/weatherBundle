<?php

namespace NfqWeatherBundle\Provider;

use NfqWeatherBundle\Location;
use NfqWeatherBundle\Weather;

class YahooWeatherProvider implements WeatherProviderInterface
{
    public function fetch(Location $location): Weather
    {
        $BASE_URL = "http://query.yahooapis.com/v1/public/yql";

        $yql_query = 'select * from weather.forecast where woeid in (SELECT woeid FROM geo.places WHERE text="(' . $location->getLat() . ',' . $location->getLon() . ')") and u=\'c\'';
        $yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&format=json";

        $session = \curl_init($yql_query_url);
        curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
        $json = curl_exec($session);
        $phpObj =  json_decode($json);

        if(empty($phpObj->query->results->channel->item->condition->temp)) {
            throw new WeatherProviderException('Weather data fetch failed');
        }

        return new Weather((float)$phpObj->query->results->channel->item->condition->temp);
    }
}