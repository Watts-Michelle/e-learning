<?php

class O_HTTPResponse_Exception extends SS_HTTPResponse_Exception {

	/**
	 * @param  string|SS_HTTPResponse body Either the plaintext content of the error message, or an SS_HTTPResponse
	 *                                     object representing it.  In either case, the $statusCode and
	 *                                     $statusDescription will be the HTTP status of the resulting response.
	 * @see SS_HTTPResponse::__construct();
	 */
	public function __construct($body = null, $statusCode = null, $statusDescription = null) {
		$message = (new JsonApi)->formatReturn(['status' => 'error', 'code' => $statusDescription, 'message' => $body ? $body : 'no message found']);
		parent::__construct($message, $statusCode);
	}

}