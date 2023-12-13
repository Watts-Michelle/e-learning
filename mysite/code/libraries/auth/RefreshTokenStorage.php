<?php

use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\RefreshTokenInterface;

class RefreshTokenStorage extends AbstractStorage implements RefreshTokenInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function get($token)
	{

		$result = OauthRefreshToken::get()->filter(['RefreshToken' => $token])->first();

		if ($result) {
			$token = (new RefreshTokenEntity($this->server))
				->setId($result->RefreshToken)
				->setExpireTime(strtotime($result->ExpireTime))
				->setAccessTokenId($result->OauthAccessToken()->AccessToken);

			return $token;
		}

		return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function create($token, $expireTime, $accessToken)
	{
		$accessTokenObj = OauthAccessToken::get()->filter('AccessToken', $accessToken)->first();
		$refreshToken = new OauthRefreshToken();
		$refreshToken->RefreshToken = $token;
		$refreshToken->ExpireTime = date('Y-m-d H:i:s', $expireTime);
		$refreshToken->OauthAccessTokenID = $accessTokenObj->ID;
		$refreshToken->write();
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(RefreshTokenEntity $token)
	{
		$result = OauthRefreshToken::get()->filter(['RefreshToken' => $token->getId()])->first();

		if ($result) {
			$result->delete();
		}
	}
}