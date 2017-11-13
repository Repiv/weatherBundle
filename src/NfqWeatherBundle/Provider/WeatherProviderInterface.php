<?php

namespace NfqWeatherBundle\Provider;

use NfqWeatherBundle\Location;
use NfqWeatherBundle\Weather;

interface WeatherProviderInterface
{
    public function fetch(Location $location): Weather;
}
