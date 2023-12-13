<?php

use Respect\Validation\Validator as v;

class AssociateChannelForm extends FormHandler implements FormHandlerInterface {

	protected $fields;

	public function __construct($data) {

		$this->data = $data;

		$this->fields = [
			'ChannelID' => [
				'name' => 'channel_id',
				'validation' => [v::stringType()->notEmpty()]
			],
			'Platform' => [
				'name' => 'platform',
				'validation' => [v::notEmpty()->stringType()->in(['ios', 'android'])]
			]
		];
	}

	public function process() {
		return parent::process();
	}
}