<?php
/**
 * View JWT token
 *
 */

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Elgg\IndieWeb\IndieAuth\Entity\IndieAuthToken;

$guid = (int) elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'object', IndieAuthToken::SUBTYPE);

/* @var $entity IndieAuthToken */
$entity = get_entity($guid);

if (!$entity->canEdit()) {
	throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

$title = elgg_echo('indieweb:indieauth:view_jwt:title');

$content = elgg_format_element('div', ['class' => 'mbm'], elgg_echo('indieweb:indieauth:view_jwt:info'));

$signer = new Sha512();

try {
    $created = new \DateTimeImmutable();
    $created->setTimestamp($entity->getCreated());
} catch (\Exception $ignored) {}

$key = Key\InMemory::plainText(file_get_contents(elgg_get_plugin_setting('indieauth_private_key', 'indieweb')));

$config = Configuration::forSymmetricSigner($signer, $key);

$JWT = $config->builder()
    ->issuedBy(elgg_get_site_url())
    ->permittedFor($entity->getClientId())
    ->identifiedBy($entity->getCreated())
    ->issuedAt($created)
    ->withClaim('target_id', $entity->getOwnerId())
    ->getToken($config->signer(), $config->signingKey());

$content .= elgg_format_element('pre', [], elgg_view('output/longtext', [
	'value' => $JWT->toString()
]));

echo elgg_view_module('info', $title, $content);

