<?php

namespace NfqWeatherBundle\Provider;

use NfqWeatherBundle\Location;
use NfqWeatherBundle\Weather;
use Psr\Cache\CacheItemPoolInterface;

class CachedWeatherProvider implements WeatherProviderInterface
{
    private $cache;
    private $provider;
    private $ttl;

    public function __construct(CacheItemPoolInterface $cache, $provider, int $ttl)
    {
        $this->cache = $cache;
        $this->provider = $provider;
        $this->ttl = $ttl;
    }

    public function fetch(Location $location): Weather
    {
        $cache = $this->cache;
        $key = $this->generateKey($location);

        $temperature = $cache->getItem($key);

        if(!$temperature->isHit()) {
            try {
                $weather = $this->provider->fetch($location);

                $this->setCache($location, $weather);

                return $weather;
            } catch (WeatherProviderException $e) {
                throw new WeatherProviderException('Weather data fetch failed');
            }
        }

        return new Weather($temperature->get());
    }

    public function setCache(Location $location, Weather $weather)
    {
        $cache = $this->cache;
        $key = $this->generateKey($location);

        $temperature = $cache->getItem($key);

        if(!$temperature->isHit()) {

            $temperature->expiresAfter($this->ttl);
            $temperature->set($weather->getTemperature());

            $cache->save($temperature);
        }
    }

    private function generateKey(Location $location)
    {
        return 'temperature.' . $location->getLat() . '.' . $location->getLon();
    }
}