<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Base controller for API
 *
 * Contains common functions used across multiple methods in the API
 * Also handles authentications for other Controllers
 *
 * @package Studytracks
 * @subpackage Controllers
 * @author Jonathan Little <jonathan@flipsidegroup.com>
 */
class Base_Controller extends Controller {

	/** @var Member The logged in user */
	protected $appUser;

	/** @var  array Decoded JSON body from request */
	protected $requestBody;

	/** @var  AuthResource The login server */
	protected $authServer;

	/** @var bool Does this controller use auth? */
	protected $auth = true;

	protected $settings;

	protected $unverified = array();

	/** @var array defining allowed actions for this controller */
	private static $allowed_actions = array();

	/** @var array defining URL rules for this controller */
	private static $url_handlers = array();

	/** @var  \Monolog instance */
	protected $logger;

	/**
	 * Assign the request body to variable
	 * Authenticate the user if the controller requires it
	 */
	public function init() {
		parent::init();

		$this->settings = SiteConfig::current_site_config();

		if ($this->auth) {
			//check authentication
			try {
				$this->authServer = new AuthResource;
				$this->appUser = $this->authServer->getLoggedInUser();
				CurrentUser::setUser($this->appUser);

				//make sure the user still has permission
				//if (! Permission::checkMember($this->appUser, 'APP_CAN_LOGIN')) throw new League\OAuth2\Server\Exception\AccessDeniedException('User not in the correct group');

				if ($this->appUser->SchoolID) {
					if ($this->appUser->School()->Suspended) {
						$this->handleError(1006, 'School account suspended', 401);
					}
				}

			} catch (League\OAuth2\Server\Exception\InvalidRequestException $e) {
				$this->handleError(401, 'Authentication header missing or invalid');
			} catch (League\OAuth2\Server\Exception\AccessDeniedException $e) {
				$this->handleError(1002, 'Session invalid', 401);
			}

			if (! in_array($this->request->getURL(), $this->unverified) && ! $this->appUser->Verified) {

				if (
					$this->appUser->VerificationCode == 0 ||
					$this->appUser->VerificationExpiry == NULL ||
					$this->appUser->VerificationExpiry < date('Y-m-d H:i:s')
				) {
					$this->generateVerificationCode($this->appUser, true);
				}

				$this->handleError(1003, 'Account must be verified');
			}

			$this->appUser->LastAccess = date('Y-m-d H:i:s');
			$this->appUser->write();
		}

		$this->requestBody = $this->getBody($this->request);

		if (!empty($this->settings->LogDirectory)) {
			$this->logger = new Logger('name');
			$this->logger->pushHandler(new StreamHandler($this->settings->LogDirectory, Logger::DEBUG));
		}
	}

	/**
	 * @param int $errorCode Error number
	 * @param string $message Error message
	 * @param int $httpStatus Error status
	 * @throws O_HTTPResponse_Exception
	 * @return bool
	 */
	public function handleError($errorCode, $message = null, $httpStatus = 400) {
		if ($errorCode == 404) $httpStatus = 404;
		if ($errorCode == 401) $httpStatus = 401;

		$errorCodeObj = new ErrorCodes;

		if (! $message) {
			$message = $errorCodeObj->config()->{$errorCode};
		}

		throw new O_HTTPResponse_Exception($message, $httpStatus, $errorCode);
		return false;
	}

	/**
	 * Get request body from POST data
	 *
	 * @param SS_HTTPRequest $request
	 * @return mixed
	 */
	public function getBody(SS_HTTPRequest $request) {
		$body = $request->getBody();
		return json_decode($body, true);
	}

	protected function generateVerificationCode(Member $member, $sendEmail = false)
	{
		$digits = 6;
		$member->VerificationCode = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
		$member->VerificationExpiry = date('Y-m-d H:i:s', strtotime('+2 days'));
		$member->write();

		if ($sendEmail) {
			if($member->School()->French || $member->ExamCountry()->Name == 'France'){
				$member->sendFrenchVerificationEmail();
			} else {
				$member->sendVerificationEmail();
			}
		}
	}
}