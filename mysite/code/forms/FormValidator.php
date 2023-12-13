<?php

use Respect\Validation\Validator as v;

class FormValidator {

	protected $fields;
	protected $data;
	protected $validation = array();

	public function process() {

		foreach ($this->fields as $key => $field) {
			if (isset($field['validation'])) {
				if (! is_array($field['validation'])) {
					$field['validation'] = array($field['validation']);
				}

				foreach ($field['validation'] as $validation_row) {
					try {
						$validation_row->setName($field['name'])->check(isset($this->data[$field['name']]) ? $this->data[$field['name']] : '');
					} catch (exception $e) {
						$this->validation[] = $e->getMainMessage();
					}
				}
			}
		}

		return $this->validation;
	}


	/**
	 * Update $this->fields with a small update
	 * This is useful if a form has required fields only in certain situations
	 *
	 * For example when creating an event, a name may be required when creating the event
	 * However, when updating, we could just send back the fields that have changed instead of the whole record
	 * This means that name should no longer be required
	 *
	 * param array $field_updates
	 */
	public function updateValidation($field_updates) {

		foreach ($field_updates as $field => $updates) {

			if (isset($updates['name'])) {
				$this->fields[$field]['name'] = $updates['name'];
			}

			if (isset($updates['validation'])) {
				foreach ($updates['validation'] as $rule) {
					$this->fields[$field]['validation'][] = $rule;
				}
			}
		}

	}

}