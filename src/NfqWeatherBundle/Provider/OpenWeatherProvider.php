<?php

namespace NfqWeatherBundle\Provider;

use NfqWeatherBundle\Location;
use NfqWeatherBundle\Weather;

class OpenWeatherProvider implements WeatherProviderInterface
{
    private $key;

    public function fetch(Location $location): Weather
    {
        if(!$this->getKey()) {
            throw new WeatherProviderException('Api key missing');
        }

        $url = 'api.openweathermap.org/data/2.5/weather?units=metric&lat=' . $location->getLat() . '&lon=' . $location->getLon() . '&APPID=' . $this->getKey();

        $session = curl_init($url);
        curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
        $json = curl_exec($session);
        $phpObj =  json_decode($json);

        if(empty($phpObj->main->temp)) {
            throw new WeatherProviderException('Weather data fetch failed');
        }

        return new Weather($phpObj->main->temp);
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }
}