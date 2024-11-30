<?php
/**
 * IndieWeb
 * @author Nikolai Shcherbin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2022
 * @link https://wzm.me
**/

namespace Elgg\IndieWeb\Microsub\Client;

use Elgg\Traits\Di\ServiceFacade;

class ApertureClient {
	
	use ServiceFacade;
	
	/**
	 * {@inheritdoc}
	 */
	public static function name() {
		return 'aperture';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function __get($name) {
		return $this->$name;
	}

	/**
	 * {@inheritdoc}
     */
	public function sendPost($api_key, $post) {
		$this->sendMicropubRequest($api_key, $post);
	}

	/**
     * Send micropub request.
     *
     * @param $api_key
     *   The Aperture Channel API key.
     * @param $post
     *   The micropub post to send.
     */
    public function sendMicropubRequest($api_key, $post) {
		$auth = 'Bearer ' . $api_key;
		
		$client = elgg()->httpClient->setup();
		
		$headers = [
			'Accept' => 'application/json',
		];

		// Access token is always in the headers when using Request from p3k.
		$headers['Authorization'] = $auth;

		try {
			$response = $client->post('https://aperture.p3k.io/micropub', ['json' => $post, 'headers' => $headers]);
			$status_code = $response->getStatusCode();
			$headersLocation = $response->getHeader('Location');
			if (empty($headersLocation[0]) || $status_code != 201) {
				elgg_log('Error sending micropub request: ' . $status_code, 'ERROR');
				return false;
			}
		}
		catch (\Exception $e) {
			elgg_log('Error sending micropub request: ' . $e->getMessage(), 'ERROR');
			return false;
		}
	}
}