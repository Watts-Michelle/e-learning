<?php

use Respect\Validation\Validator as v;

class PurchaseForm extends FormHandler implements FormHandlerInterface {

	protected $fields;

	public function __construct($data) {

		$this->data = $data;

		$this->fields = [
			'OrderNumber' => [
				'name' => 'order',
				//'validation' => [v::stringType()->notEmpty()]
				'validation' => [v::stringType()]
			],
			'IOSSKU' => [
				'name' => 'ios_sku',
				'validation' => [v::stringType()]
			],
			'AndroidSKU' => [
				'name' => 'android_sku',
				'validation' => [v::stringType()]
			],
		];
	}
}