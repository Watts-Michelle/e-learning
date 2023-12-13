<?php

class WestAuthentication_Controller extends Base_Controller {

    protected $auth = false;

    protected $redirect_uri = '/api/west/auth/login';

    private static $allowed_actions = array(
        'login'
    );

    private static $url_handlers = array(
        'login' => 'login'
    );

    /**
     * Login retrieves authentication code.
     *
     * @param  SS_HTTPRequest $request
     * @return mixed|SS_HTTPResponse
     * @throws Exception
     */
    public function login(SS_HTTPRequest $request)
    {
        if($code = $request->getVar('code')){
            $array = $this->processIncomingRequests($code, $this->setOptions());

            return print_r($array);
        }

        return (new JsonApi)->formatReturn([]);
    }

    /**
     * Prepares options common to interacting with West's authentication & API.
     *
     * @param  array|NULL $override_options
     * @return array
     * @throws Exception
     */
    function setOptions(array $override_options = NULL)
    {
        $options = array(
            'client_id' => WestSSOClientID,
            'client_secret' => WestSSOClientSecret,
            'west_redirect_base' => Director::absoluteBaseURL().$this->redirect_uri,
            'west_oauth_base' => 'https://passport.schoolmessenger.com/oauth',
            'west_api_base' => 'https://passport.schoolmessenger.com',
        );

        if (isset($override_options)) {
            array_merge($options, $override_options);
        }

        $options['west_oauth_tokens_url'] = $options['west_oauth_base'] . "/token";
        $options['west_oauth_authorize_url'] = $options['west_oauth_base'] . "/auth";
        $options['west_api_me_url'] = $options['west_api_base'] . '/services/v1.4/users/me';
        $options['client_redirect_url'] = $options['west_redirect_base'];

        if (!empty($options['client_id']) && !empty($options['client_secret']) && !empty($options['west_redirect_base'])) {
            return $options;
        } else {
            throw new Exception("Cannot communicate with West without configuration.");
        }
    }

    /**
     * Services requests based on the incoming request path.
     *
     * @param  $code
     * @param  array $options
     * @return SS_HTTPResponse
     */
    function processIncomingRequests($code, array $options)
    {
        if($code) {
            try {
                $me = $this->processClientRedirect($code, $options);
                $schoolID = $me['data']['school'];
                $staffID = $me['data']['id'];

                if($school = School::get()->filter('WestID', $schoolID)->first()) {
                    foreach ($school->Staff() as $staff) {
                        if ($staff->WestID == $staffID) {
                            $staff->logIn();
                            return $this->redirect(Director::absoluteBaseURL() . 'school');
                        } else {
                            echo 'Staff member does not exist in that school.';
                        }
                    }
                } else {
                    echo 'That school does not exist.';
                }

            } catch (Exception $e) {

                echo("<p>Something exceptional happened while interacting with West.");
                echo("<pre>");
                print_r($e);
                echo("</pre>");
            }

        } else {
            // Our home page route will create a West Instant Login button for users
//            $sign_in_link = $this->generate_sign_in_with_west_link($options);
//            echo("<h1>west_oauth_examples: Login!</h1>");
//            echo('<p>' . $sign_in_link . '</p>');
            echo("<p>Ready to handle OAuth 2.0 client redirects on {$options['client_redirect_url']}.</p>");
        }
    }

    /**
     * Processes incoming requests to our $client_redirect.
     *
     * 1. Exchanges incoming code parameter for a bearer token
     * 2. Uses bearer token in a request to West's "/me" API resource
     *
     * @param  $code             OAuth 2.0 exchange code received when our  OAuth redirect was triggered.
     * @param  array $options    Options used for West API requests.
     * @return array
     * @throws Exception
     */
    function processClientRedirect($code, array $options)
    {
        $bearer_token = $this->exchangeCodeForBearerToken($code, $options);
        $request_options = array('method' => 'GET', 'bearer_token' => $bearer_token);
        $me_response = $this->retrieveMeResponseForBearerToken($bearer_token, $options);

        // Real world applications would store the bearer token and relevant information about the user at this stage.
        return $me_response;
    }

    /**
     * Exchanges a $code value received in a $client_redirect for a bearer token.
     *
     * @param  string $code             OAuth 2.0 exchange code received when our OAuth redirect was triggered.
     * @param  array $options           Options used for West API requests.
     * @return mixed
     * @throws Exception
     */
    function exchangeCodeForBearerToken($code, array $options)
    {
        $data = array(
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $options['client_redirect_url']
        );

        $request_options = array('method' => 'POST', 'data' => $data);

        $response = $this->requestFromWest($options['west_oauth_tokens_url'], $request_options, $options);

        // Evaluate if the response is successful
        if ($response && $response['response_code'] && $response['response_code'] == '200') {

            $bearer_token = $response['response']['access_token'];
            return $bearer_token;

        } else {
            // Handle condition when $code cannot be exchanged for bearer token from West
            throw new Exception("Cannot retrieve bearer token.");
        }
    }

    /**
     * Uses the specified bearer token to retrieve the /me response for the user.
     *
     * @param  string $bearer_token      The string value of a user's OAuth 2.0 access token.
     * @param  array $options            Options used for West API requests.
     * @return mixed
     * @throws Exception
     */
    function retrieveMeResponseForBearerToken($bearer_token, array $options)
    {
        $request_options = array('method' => 'GET', 'bearer_token' => $bearer_token);

        $response = $this->requestFromWest($options['west_api_me_url'], $request_options, $options);

        // Evaluate if the response is successful
        if ($response && $response['response_code'] && $response['response_code'] == '200') {

            $oauth_response = $response['response'];
            return $oauth_response;

        } else {

            // Handle condition when /me response cannot be retrieved for bearer token
            throw new Exception("Could not retrieve /me response for bearer token.");
        }
    }

    /**
     * General-purpose HTTP wrapper for working with the West API.
     *
     * @param  string $url                      The fully-qualified URL that the request will be issued to.
     * @param  array $request_options           Hash of options pertinent to the specific request.
     * @param  array $west_options              Hash of options more generally associated with West API requests.
     * @return array
     * @throws Exception
     */
    function requestFromWest($url, array $request_options, array $west_options)
    {
        $ch = curl_init($url);

        $request_headers = array('Accept: application/json');

        if ($request_options && array_key_exists('bearer_token', $request_options)) {

            $auth_header = 'Authorization: Bearer ' . $request_options['bearer_token'];
            $request_headers[] = $auth_header;

        } else {
            // When we don't have a bearer token, assume we're performing client auth.
            curl_setopt($ch, CURLOPT_USERPWD, $west_options['client_id'] . ':' . $west_options['client_secret']);
        }

        if ($request_options && array_key_exists('method', $request_options) && $request_options['method'] == 'POST') {

            curl_setopt($ch, CURLOPT_POST, 1);

            if ($request_options['data']) {

                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_options['data']));
                $content_type_header = 'Content-Type: application/x-www-form-urlencoded';
                $request_headers[] = $content_type_header;
            }
        }

        // Set prepared HTTP headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $raw_response = curl_exec($ch);
        $parsed_response = json_decode($raw_response, true);
        $curl_info = curl_getinfo($ch);

        // Provide the HTTP response code for easy error handling.
        $response_code = $curl_info['http_code'];

        if($curl_error = curl_errno($ch)) {
            $error_message = curl_strerror($curl_error);
            throw new Exception("cURL failure #{$curl_error}: {$error_message}");
        }

        // Prepare the parsed and raw response for further use.
        $normalized_response = array('response_code' => $response_code, 'response' => $parsed_response, 'raw_response' => $raw_response, 'curl_info' => $curl_info);
        return $normalized_response;
    }
}
