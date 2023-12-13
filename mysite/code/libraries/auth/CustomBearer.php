<?php
/**
 * OAuth 2.0 Bearer Token Type
 *
 * @package     league/oauth2-server
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 * @link        https://github.com/thephpleague/oauth2-server
 */

use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Server\TokenType\Bearer as Bearer;
use League\OAuth2\Server\TokenType\TokenTypeInterface as TokenTypeInterface;

class CustomBearer extends Bearer implements TokenTypeInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generateResponse(){

		$return = parent::generateResponse();
		$return['exists'] = $this->getParam('exists');

		return $return;
	}

}
