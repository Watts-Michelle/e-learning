<?php

class FormHandler extends FormValidator implements FormHandlerInterface {

	protected $fields;

	public function __construct($data) {}

	public function process() {
		//process the data and check that rules defined above are met successfully
		$errors = parent::process();

		if (! empty($errors)) {
			throw new O_HTTPResponse_Exception(implode(', ', $errors), 400, 2001);
		}

		$data_array = array();

		// Create the new array of data
		foreach ($this->fields as $key => $array) {

			if ($key == 'GroupTests') continue;

			if (isset($this->data[$array['name']])) {
				if (!is_array($this->data[$array['name']])) {
					$data_array[$key] = trim($this->data[$array['name']]);
				} else {
					$data_array[$key] = $this->data[$array['name']];
				}
			}
		}

		return $data_array;
	}

}
