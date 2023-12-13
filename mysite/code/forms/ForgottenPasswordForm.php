<?php

use Respect\Validation\Validator as v;

class ForgottenPasswordForm extends FormHandler implements FormHandlerInterface {

	protected $fields;

	public function __construct($data) {

		$this->data = $data;

		$this->fields = [
			'Email' => [
				'name' => 'email',
				'validation' => [v::email()->notEmpty()]
			],
		];
	}
}