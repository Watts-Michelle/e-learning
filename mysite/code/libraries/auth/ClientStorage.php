<?php

use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ClientInterface;

class ClientStorage extends AbstractStorage implements ClientInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null) {

		$public = API_CLIENT_PUBLIC;
		$secret = API_CLIENT_SECRET;

		if (! empty($public) && ! empty($secret) && $public == $clientId && $secret == $clientSecret) {
			$client = new ClientEntity($this->server);

			$client->hydrate([
				'id'    =>  1,
				'name'  =>  $public,
			]);

			return $client;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBySession(SessionEntity $session) {

		$public = API_CLIENT_PUBLIC;
		$secret = API_CLIENT_SECRET;

		if (! empty($public)) {
			$client = new ClientEntity($this->server);

			$client->hydrate([
				'id'    =>  1,
				'name'  =>  $public,
			]);

			return $client;
		}

		return false;
	}
}
