<?php

use Respect\Validation\Validator as v;

class PlaylistForm extends FormHandler implements FormHandlerInterface {

	protected $fields;

	public function __construct($data) {

		$this->data = $data;

		$this->fields = [
			'Name' => [
				'name' => 'name',
				'validation' => [v::stringType()->notEmpty()]
			],
			'Lessons' => [
				'name' => 'lesson',
				'validation' => [v::optional(v::arrayType())]
			],
		];
	}
}