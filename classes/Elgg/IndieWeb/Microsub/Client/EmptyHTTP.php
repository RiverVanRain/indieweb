<?php

/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub\Client;

class EmptyHTTP
{
    public function get($url = null, $options = null)
    {
        return [
            'error' => true,
        ];
    }
}
