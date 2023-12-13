<?php

class Clever_Controller extends Base_Controller
{
	private $token = '92839375d312b63e80ac73c1de8cca39d7129eab';

	protected $auth = false;

	protected $client_id = '0c539070fc55debe88ff';

	protected $client_secret = '25b71e5c2148008ae09488df59c0c5e040c2eb4d';

	protected $redirect_uri = 'http://studytracks.dev.flipsidegroup.com/api/clever/auth/login';

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'callback',
		'CleverLogin'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'callback',
		'login' => 'CleverLogin'
	);

	public function callback()
	{
		ini_set('max_execution_time', 0);

		Clever::setToken($this->token);

		$Test = [];

		$Test['test'] = CleverSchool::all();

		$Test['district'] = CleverDistrict::all();

		return (new JsonApi)->formatReturn([$Test]);
	}

//	public function CleverLogin(SS_HTTPRequest $request)
//	{
//		if (! $request->isPOST()) $this->handleError(404, 'not found');
//
//		$data = $this->getBody($request);
//	}

	/**
	 * POST-ing to Clever. In this integration, this is only used for exchanging Authorization
	 * Codes for IL Bearer Tokens
	 * This function returns the full response, and a separate function (getToken) extracts the token
	 *
	 * @param $endpoint
	 * @param $data
	 * @param SS_HTTPRequest $request
	 * @return mixed
	 */
//	public function cleverPOST($endpoint, $data)
//	{
////		r = requests.post(endpoint, data=data, auth=(client_id, client_secret))
////		return r
//
//		$request = new SS_HTTPRequest('POST', $endpoint);
//		$request->addHeader('X-Pjax', 'alpha');
//		$request->addHeader('Accept', 'text/json');
//
//		$body = $request->getBody();
//		return json_decode($body, true);
//	}

	/**
	 * GET-ting information from the Clever API. In this integration, this is used both to GET
	 * basic information about the user from the Identity API and to query the Data API for user data.
	 *
	 * This function returns the API response as a JSON object. Separate functions are used to process the data thereafter
	 *
	 * @param $endpoint
	 * @param $token
	 */
//	public function cleverGET($endpoint, $token)
//	{
//		$headers = array(
//			"Authorization" => "Bearer %s" % $token
//		);
//
////		r = requests.get(endpoint, headers=headers)
//
////		if r.status_code == 200 {
////			return r.json()
////			print r.text
////		}
////		return None
//	}

	/**
	 * This function takes in a code from the redirect URI and attempts to exchange it for a bearer token.
	 *
	 * @param $code
	 */
//	public function getToken($code)
//	{
//		$api_endpoint = 'https://clever.com/oauth/tokens';
//
//		$data = array(
//			'code' => $code,
//			'grant_type' => 'authorization_code',
//			'redirect_uri' => $this->redirect_uri,
//		);
//
//		$r = $this->cleverPOST($api_endpoint, $data);
//
//		if ($r->status_code == 200) {
//			$token = $r->json()['access_token'];
//			return $token;
//			//print "POST attempt failed";
//		}
////		return None
//	}

//	public function IL(SS_HTTPRequest $request)
//	{
//		$code = $request->getVar('code') ? $request->getVar('code') : '' ;
//
//		$scope = $request->getVar('scope') ? $request->getVar('scope') : '' ;
//
//		$ilToken = $this->getToken($code);
//
//		$me = $this->cleverGET('https://api.clever.com/me/'.$ilToken);
//
////		var_dump($me);
//
//		if(!$me){
////			return 'request failed';
//		}
//
//	}
}