<?php

/**
 * Prepare Json Response
 *
 * Prepare $data to be returned to the user
 *
 * @package Studytracks
 * @subpackage Libraries
 * @author Jonathan Little <jonathan@flipsidegroup.com>
 */
class JsonApi
{

	/**
	 * Send a json response using the API
	 *
	 * @param array $data Data you want to send in the response
	 * @return SS_HTTPResponse
	 */
	public function formatReturn(array $data)
	{

		if (empty($data['status'])) {
			$data['status'] = 'success';
		}

		$response = new SS_HTTPResponse(json_encode($data, JSON_UNESCAPED_UNICODE));
		$response->addHeader('Content-type', "application/json; charset=utf-8");
		
		return $response;
	}

}