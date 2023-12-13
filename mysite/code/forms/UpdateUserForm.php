<?php

use Respect\Validation\Validator as v;

class UpdateUserForm extends FormHandler implements FormHandlerInterface {

	protected $fields;

	public function __construct($data) {

		$gender = DB::get_schema()->enumValuesForField(new Member, 'Gender');
		$ethnicity = Ethnicity::get()->map('ID', 'Name')->toArray();
		$ethnicity = array_merge($ethnicity, array_keys($ethnicity));
		$country = Country::get()->map('ID', 'TwoCharCode')->toArray();
		$examCountry = ExamCountry::get()->map('ID', 'Name')->toArray();
		$examLevel = ExamLevel::get()->filter(['Live' => 1])->map('ID', 'Name')->toArray();

		$this->data = $data;

		$this->fields = [
			'Email' => [
				'name' => 'email',
				'validation' => [v::optional(v::email()->noWhitespace())]
			],
			'Password' => [
				'name' => 'password',
				'validation' => [v::stringType()->noWhitespace()->optional(v::length(6))]
			],
			'Username' => [
				'name' => 'username',
				'validation' => [v::stringType()->not(v::email())]
			],
			'FirstName' => [
				'name' => 'firstname',
				'validation' => [v::stringType()]
			],
			'Surname' => [
				'name' => 'lastname',
				'validation' => [v::stringType()]
			],
			'Gender' => [
				'name' => 'gender',
				'validation' => [v::stringType()->optional(v::in($gender))]
			],
			'DateOfBirth' => [
				'name' => 'date_of_birth',
				'validation' => [v::optional(v::numeric())]
			],
			'Country' => [
				'name' => 'country',
				'validation' => [v::stringType()->optional(v::in($country))]
			],
			'Ethnicity' => [
				'name' => 'ethnicity',
				'validation' => [v::stringType()->optional(v::in($ethnicity))]
			],
			'ExamLevel' => [
				'name' => 'exam_level',
				'validation' => [v::stringType()->optional(v::in($examLevel))]
			],
			'ExamCountry' => [
				'name' => 'exam_country',
				'validation' => [v::stringType()->optional(v::in($examCountry))]
			],
			'Device' => [
				'name' => 'device',
				'validation' => [v::stringType()]
			],
		];
	}
}