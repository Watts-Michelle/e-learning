<?php

use GuzzleHttp\Client as Client;

class UrbanAirship {

	private $response_status;

	public function __construct() {
		if (empty(URBANAIRSHIP_API_URL)) throw new \Exception('Missing UA URL');
		if (empty(URBANAIRSHIP_API_KEY)) throw new \Exception('Missing UA Key');
		if (empty(URBANAIRSHIP_API_SECRET)) throw new \Exception('Missing UA Secret');
		if (empty(URBANAIRSHIP_API_MASTER_SECRET)) throw new \Exception('Missing UA Master Secret');
	}

	/**
	 * Associate a device id to a user
	 * @param int $user_id
	 * @param int $channel_id
	 * @param string $device_type
	 *
	 * @throws |Exception
	 * @return array
	 */
	public function associate($user_id, $channel_id, $device_type) {
		$result = $this->call(
			'POST',
			'api/named_users/associate',
			[
				'channel_id' => $channel_id,
				'device_type' => $device_type,
				'named_user_id' => 'user'.$user_id
			]
		);

		if ($result->ok) return true;
		throw new \Exception($result->error);
	}

	/**
	 * Check if this user is already associated with a named_user on urban airship
	 * @param $user_id
	 * @return bool
	 * @throws \Exception
	 */
	public function check ($user_id) {
		$result = $this->call(
			'GET',
			'api/named_users/',
			['id' => 'user'.$user_id]
		);

		if ($result->ok) return true;
		return false;
	}

	/**
	 * Disassociate a device id from a user
	 */
	public function disassociate($channel_id, $device_type) {
		$result = $this->call(
			'POST',
			'api/named_users/disassociate',
			[
				'channel_id' => $channel_id,
				'device_type' => $device_type
			]
		);

		if ($result->ok) return true;
		return false;
	}

	/**
	 * Send a push notification to a user
	 * @param $user
	 * @param $message
	 * @param $notification_count
	 * @param $message_count
	 * @param $notification_type
	 * @return bool
	 * @throws \Exception
	 */
	public function sendPushToUser($user, $message, $notification_count = null, $message_count = 0, $notification_type = 'notification') {

		$request = [
			'audience' => ['named_user' => $this->namedUsers($user)],
			'notification' => [
				'ios' => [
					'badge' => is_numeric($notification_count) ? $notification_count : '+1',
					'alert' => $message,
					'sound' => 'default',
					'extra' => [
						'message_count' => $message_count,
						'notification_type' => $notification_type
					]
				]
			],
			'device_types' => ['ios']
		];

		return $this->sendPush($request);
	}

	/**
	 * Send a push to all users
	 * @param $message
	 * @return bool
	 * @throws \Exception
	 */
	public function sendPushToAll($message) {

		$request = [
			'audience' => 'all',
			'notification' => ['alert' => $message],
			'device_types' => ['ios']
		];

		return $this->sendPush($request);
	}

	public function sendBackgroundNotification($user, $notification_type) {

		$request = [
			'audience' => ['named_user' => $this->namedUsers($user)],
			'notification' => [
				'ios' => [
					'priority' => 5,
					'extra' => [
						'notification_type' => $notification_type
					]
				]
			],
			'device_types' => ['ios']
		];

		return $this->sendPush($request);
	}

	private function sendPush($push) {
		$result = $this->call(
			'POST',
			'api/push',
			$push
		);

		if ($result->ok) return true;
		throw new \Exception($result->error);
	}

	/**
	 * Create a guzzle request and send it to urbanairship using the config we have
	 * @param $type
	 * @param $url
	 * @param array $data
	 * @return mixed
	 * @throws \Exception
	 */
	private function call($type, $url, array $data = []) {

		$array = ['auth' => [URBANAIRSHIP_API_KEY, URBANAIRSHIP_API_MASTER_SECRET]];
		$array['headers'] = ['accept' => 'application/vnd.urbanairship+json; version=3;'];

		if (!empty($data) && $type == 'POST') {
			$array['json'] = $data;
		}

		if (!empty($data) && $type == 'GET') {
			$array['query'] = $data;
		}

		$client = new Client(['base_uri' => URBANAIRSHIP_API_URL]);
		$response = $client->send($client->createRequest($type, URBANAIRSHIP_API_URL . $url, $array));
		$this->response_status = $response->getStatusCode();

		return json_decode($response->getBody());
	}

	private function namedUsers($user) {
		if (! is_array($user)) {
			return 'user'.$user;
		} else {
			$users = [];

			foreach ($user as $u) {
				$users[] = 'user'.$u;
			}

			return $users;
		}
	}
}