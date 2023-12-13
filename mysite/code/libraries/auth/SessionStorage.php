<?php

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;

class SessionStorage extends AbstractStorage implements SessionInterface {

	/**
	 * {@inheritdoc}
	 */
	public function getByAccessToken(AccessTokenEntity $accessToken)
	{
		$aToken = OauthAccessToken::get()->filter(['AccessToken' => $accessToken->getId()])->first();
		$result = OauthSession::get()->byID($aToken->OauthSessionID);

		if ($result) {
			$session = new SessionEntity($this->server);
			$session->setId($result->ID);
			$session->setOwner('dev', $result->Member()->UUID);
			return $session;
		}

		throw new Exception('Session not found');
	}

	/**
	 * {@inheritdoc}
	 */
	public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
	{
		$session = new OauthSession();
		$session->MemberID = $ownerId;
		$session->write();

		return $session->ID;
	}

	/**
	 * Using an owner id, remove a user's access tokens and refresh tokens
	 * We will keep their sessions as a record
	 * @param $ownerId
	 */
	public function removeAll($ownerId) {

		$sessions = OauthSession::get()->filter(['MemberID' => $ownerId]);

		foreach ($sessions as $session) {
			$accessTokens = $session->OauthAccessTokens();

			foreach ($accessTokens as $token) {
				$this->removeAccessToken($token);
			}

			$session->delete();
		}
	}


	/**
	 * Remove a single access token (ie device logout)
	 * @param $access_token
	 */
	public function removeAccessToken(OauthAccessToken $accessToken) {
		$refreshTokens = $accessToken->OauthRefreshTokens();

		foreach ($refreshTokens as $refreshToken) {
			$refreshToken->delete();
		}

		$accessToken->OauthSession()->delete();
		
		$accessToken->delete();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getByAuthCode(AuthCodeEntity $authCode)	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getScopes(SessionEntity $session)
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function associateScope(SessionEntity $session, ScopeEntity $scope)
	{

	}

}
