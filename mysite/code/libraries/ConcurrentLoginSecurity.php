<?php

/**
 * Class ConcurrentLoginSecurity
 * Ensure that a user cannot log in on more devices than are allowed in the CMS
 */
class ConcurrentLoginSecurity
{

	/**
	 * Limit logins
	 * A CMS variable (SettingsConfig->ConcurrentLogins) defines how many devices a user can be logged in on concurrently
	 * 
	 * @param Member $member
	 * @return bool
	 */
	public static function check(Member $member)
	{
		$settings = SiteConfig::current_site_config();
		if ($settings->ConcurrentLogins == 0) return false;

		//clear refresh tokens starting from the oldest
		$refresh = OauthRefreshToken::get()->filter(['OauthAccessToken.OauthSession.MemberID' => $member->ID, 'ExpireTime:GreaterThan' => date('Y-m-d H:i:s')]);

		if ($refresh->count() >= $settings->ConcurrentLogins) {
			$remove = ($refresh->count() + 1) - $settings->ConcurrentLogins;

			foreach ($refresh->sort('ExpireTime ASC') as $refreshToken) {
				if ($remove == 0) break;

				$refreshToken->OauthAccessToken()->delete();
				$refreshToken->delete();

				$remove--;
			}
		}

		//clear access tokens starting from the oldest
		$access = OauthAccessToken::get()->filter(['OauthSession.MemberID' => $member->ID, 'ExpireTime:GreaterThan' => date('Y-m-d H:i:s')]);

		if ($access->count() >= $settings->ConcurrentLogins) {
			$remove = ($access->count() + 1) - $settings->ConcurrentLogins;

			foreach ($access->sort('ExpireTime ASC') as $accessToken) {
				if ($remove == 0) break;

				$accessToken->delete();
				$remove--;
			}
		}
	}

}