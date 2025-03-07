<?php

namespace Elgg\IndieWeb\IndieAuth\Client;

use GuzzleHttp\Exception\GuzzleException;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use Elgg\Http\Request;
use Elgg\Traits\Di\ServiceFacade;
use Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken;

class IndieAuthClient
{
    use ServiceFacade;

    const CERT_CONFIG = [
        'digest_alg' => 'sha512',
        'private_key_bits' => 4096,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];

    /**
    * A valid internal token.
    *
    * @var \Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken $indieAuthToken
    */
    protected $token;

    /**
    * The user guid of the token if the internal IndieAuth endpoint is used.
    *
    * @var integer
    */
    protected $tokenOwnerId;

    /**
     * {@inheritdoc}
     */
    public static function name()
    {
        return 'indieauth';
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
    * {@inheritdoc}
    */
    public function getAuthorizationHeader(Request $request)
    {
        $auth = null;
        $auth_header = $request->headers->get('Authorization');
        if ($auth_header && preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
            $auth = $auth_header;
        }

        return $auth;
    }

    /**
    * {@inheritdoc}
    */
    public function isValidToken($auth_header, $scope_to_check = null)
    {
        $token_endpoint = elgg_get_plugin_setting('indieauth_external_endpoint', 'indieweb');

        if ((bool) elgg_get_plugin_setting('enable_indieauth_endpoint', 'indieweb')) {
            return (bool) $this->validateTokenInternal($auth_header, $scope_to_check);
        } else {
            return (bool) $this->validateTokenOnExternalService($auth_header, $token_endpoint, $scope_to_check);
        }
    }

    /**
    * {@inheritdoc}
    */
    public function checkAuthor()
    {
        if ((bool) elgg_get_plugin_setting('enable_indieauth_endpoint', 'indieweb')) {
            return $this->tokenOwnerId;
        }

        return null;
    }

    /**
    * {@inheritdoc}
    */
    public function getToken()
    {
        if ($this->token) {
            return $this->token;
        }

        return false;
    }

    /**
    * {@inheritdoc}
    */
    public function revokeToken($token)
    {
        $signer = new Sha512();
        $public_key = elgg_get_plugin_setting('indieauth_public_key', 'indieweb');

        $access_token = '';

        try {
            $parser = new Parser(new JoseEncoder());
            $key = Key\InMemory::plainText(file_get_contents($public_key));
            $JWT = $parser->parse($token);

            if ($this->verifyToken($JWT, $signer, $key)) {
                $access_token = $JWT->headers()->get('jti');
            }
        } catch (\Exception $e) {
            elgg_log('Error revoking token: ' . $e->getMessage(), \Psr\Log\LogLevel::ERROR);
        }

        $indieAuthToken = elgg_call(ELGG_IGNORE_ACCESS, function () use ($access_token) {
            $indieAuthTokens = elgg_get_entities([
                'type' => 'object',
                'subtype' => IndieAuthToken::SUBTYPE,
                'limit' => false,
                'metadata_name_value_pairs' => [
                    'name' => 'access_token',
                    'value' => $access_token,
                ],
            ]);

            return count($indieAuthTokens) === 1 ? array_shift($indieAuthTokens) : null;
        });

        if ($indieAuthToken instanceof IndieAuthToken) {
            try {
                $indieAuthToken->revoke()->save();
            } catch (\Exception $ignored) {
            }
        }
    }

    /**
    * {@inheritdoc}
    */
    public function generateKeys()
    {
        if (!extension_loaded('openssl')) {
            throw new \Elgg\Exceptions\Http\BadRequestException('OpenSSL PHP extension is not loaded.');
        }

        $return = false;

        try {
            // Generate Resource
            $resource = openssl_pkey_new(self::CERT_CONFIG);

            // Get Private Key
            openssl_pkey_export($resource, $pkey);

            // Get Public Key
            $pubkey = openssl_pkey_get_details($resource);

            $keys = [
                'private' => $pkey,
                'public' => $pubkey['key'],
            ];

            $paths = [];
            $dir_path = elgg_get_data_path() . 'indieweb/indieauth';

            if (!is_dir($dir_path)) {
                mkdir($dir_path, 0755, true);
            }

            foreach (['public', 'private'] as $name) {
                // Key uri
                $key_uri = "$dir_path/$name.key";

                // Remove old key
                if (file_exists($key_uri)) {
                    unlink($key_uri);
                }

                // Write key content to key file
                file_put_contents($key_uri, $keys[$name]);

                // Set correct permission to key file
                chmod($key_uri, 0600);

                $paths[$name . '_key'] = $key_uri;
            }

            $return = $paths;
        } catch (\Exception $e) {
            elgg_log('Error generating keys: ' . $e->getMessage(), \Psr\Log\LogLevel::ERROR);
        }

        return $return;
    }

    /**
    * Verify a token.
    *
    * @param \Lcobucci\JWT\Token $JWT
    * @param \Lcobucci\JWT\Signer $signer
    * @param $key
    *
    * @return bool
    */
    private function verifyToken(Token $JWT, Signer $signer, $key)
    {
        if ($JWT->headers()->get('alg') !== $signer->algorithmId()) {
            return false;
        }

        return $signer->verify($JWT->signature()->hash(), $JWT->payload(), $key);
    }

    /**
    * Internal IndieAuth token validation.
    *
    * @param $auth_header
    * @param $scope_to_check
    *
    * @return bool
    */
    private function validateTokenInternal($auth_header, $scope_to_check)
    {
        $valid_token = false;

        $matches = [];
        $bearer_token = '';
        preg_match('/Bearer\s(\S+)/', $auth_header, $matches);
        if (isset($matches[1])) {
            $bearer_token = $matches[1];
        }

        $signer = new Sha512();
        $public_key = elgg_get_plugin_setting('indieauth_public_key', 'indieweb');

        $access_token = '';

        try {
            $parser = new Parser(new JoseEncoder());
            $key = Key\InMemory::plainText(file_get_contents($public_key));
            $JWT = $parser->parse($bearer_token);

            if ($this->verifyToken($JWT, $signer, $key)) {
                $access_token = $JWT->headers()->get('jti');
            }
        } catch (\Exception $e) {
            elgg_log('Error verifying token: ' . $e->getMessage(), \Psr\Log\LogLevel::ERROR);
        }

        // Return early already, no need to verify further.
        if (!$access_token) {
            elgg_log('No access_token', \Psr\Log\LogLevel::ERROR);
            return false;
        }

        $indieAuthToken = elgg_call(ELGG_IGNORE_ACCESS, function () use ($access_token) {
            $indieAuthTokens = elgg_get_entities([
                'type' => 'object',
                'subtype' => IndieAuthToken::SUBTYPE,
                'limit' => false,
                'metadata_name_value_pairs' => [
                    'name' => 'access_token',
                    'value' => $access_token,
                ],
            ]);
            return count($indieAuthTokens) === 1 ? array_shift($indieAuthTokens) : null;
        });

        if ($indieAuthToken instanceof IndieAuthToken && $indieAuthToken->isValid()) {
            // The token is valid
            $valid_token = true;

            // Scope check
            if (!empty($scope_to_check)) {
                $scopes = $indieAuthToken->getScopes();

                if (empty($scopes) || !in_array($scope_to_check, $scopes)) {
                    $valid_token = false;
                    elgg_log("Scope {$scope_to_check} insufficient", \Psr\Log\LogLevel::ERROR);
                }
            }

            // Update changed if valid and set owner id
            if ($valid_token) {
                $this->tokenOwnerId = $indieAuthToken->getOwnerId();
                $this->token = $indieAuthToken;
                $indieAuthToken->setMetadata('changed', time());
            }
        }

        return $valid_token;
    }

    /**
    * External IndieAuth token validation.
    *
    * @param $auth_header
    * @param $token_endpoint
    * @param $scope_to_check
    *
    * @return bool
    */
    private function validateTokenOnExternalService($auth_header, $token_endpoint, $scope_to_check)
    {
        $valid_token = false;

        try {
            $headers = [
                'Accept' => 'application/json',
                'Authorization' => $auth_header,
            ];
            $response = elgg()->httpClient->setup()->get($token_endpoint, ['headers' => $headers]);
            $json = json_decode($response->getBody());

            // Compare me with current domain. We don't support multi-user authentication yet.
            if (isset($json->me) && rtrim($json->me, '/') === elgg_get_site_entity()->getDomain()) {
                // The token is valid
                $valid_token = true;

                // Scope check.
                if (!empty($scope_to_check)) {
                    $scopes = isset($json->scope) ? explode(' ', $json->scope) : [];

                    if (empty($scopes) || !in_array($scope_to_check, $scopes)) {
                        $valid_token = false;
                        elgg_log("Scope {$scope_to_check} insufficient", \Psr\Log\LogLevel::ERROR);
                    }
                }
            }
        } catch (GuzzleException $e) {
            elgg_log('Error validating the access token: ' . $e->getMessage(), \Psr\Log\LogLevel::ERROR);
        }

        return $valid_token;
    }
}
