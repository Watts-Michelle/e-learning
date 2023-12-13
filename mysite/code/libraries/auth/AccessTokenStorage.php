<?php

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AccessTokenInterface;

class AccessTokenStorage extends AbstractStorage implements AccessTokenInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get($token)	{
		
		$result = OauthAccessToken::get()->filter(['AccessToken' => $token])->first();

		if ($result) {
			$token = (new AccessTokenEntity($this->server))
				->setId($result->AccessToken)
				->setExpireTime(strtotime($result->ExpireTime));

			return $token;
		}

		return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getScopes(AccessTokenEntity $token) {
		//we do not have multiple scopes in this project
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function create($token, $expireTime, $sessionId)
	{
		$query = new OauthAccessToken();
		$query->AccessToken = $token;
		$query->OauthSessionID = $sessionId;
		$query->ExpireTime = date('Y-m-d H:i:s', $expireTime);
		$query->write();
	}

	/**
	 * {@inheritdoc}
	 */
	public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(AccessTokenEntity $token)
	{
		$result = OauthAccessToken::get()->filter(['AccessToken' => $token->getId()])->first();

		if ($result) {
			$result->delete();
		}
	}
}
