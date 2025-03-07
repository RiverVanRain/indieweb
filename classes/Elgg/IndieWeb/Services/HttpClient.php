<?php

namespace Elgg\IndieWeb\Services;

use GuzzleHttp\Client;
use Elgg\Traits\Di\ServiceFacade;

class HttpClient
{
    use ServiceFacade;

    /**
     * {@inheritdoc}
     */
    public static function name()
    {
        return 'httpClient';
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->$name;
    }

    public function setup($options = [])
    {
        $jar = \GuzzleHttp\Cookie\CookieJar::fromArray(
            [
                elgg_get_site_entity()->getDisplayName() => elgg_get_session()->getID()
            ],
            elgg_get_site_entity()->getDomain()
        );

        $config = [
            'verify' => true,
            'timeout' => 30,
            'cookies' => $jar,
        ];
        $config = $config + $options;

        return new Client($config);
    }
}
