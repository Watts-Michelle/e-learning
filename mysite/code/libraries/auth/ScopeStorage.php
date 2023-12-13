<?php

use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ScopeInterface;

class ScopeStorage extends AbstractStorage implements ScopeInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function get($scope, $grantType = null, $clientId = null)
	{
		return false;
	}
}
