<?php

use Respect\Validation\Validator as v;

class CreateUserForm extends FormHandler implements FormHandlerInterface {

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
				'validation' => [v::stringType()->email()->notEmpty()]
			],
			'Password' => [
				'name' => 'password',
				'validation' => [v::stringType()->noWhitespace()->length(8)->notEmpty()]
			],
			'Username' => [
				'name' => 'username',
				'validation' => [v::stringType()->not(v::email())->notEmpty()]
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
				'validation' => [v::optional(v::stringType()->in($gender))]
			],
			'DateOfBirth' => [
				'name' => 'date_of_birth',
				'validation' => [v::optional(v::numeric())]
			],
			'Country' => [
				'name' => 'country',
				'validation' => [v::optional(v::in($country))]
			],
			'Ethnicity' => [
				'name' => 'ethnicity',
				'validation' => [v::optional(v::in($ethnicity))]
			],
			'ExamLevel' => [
				'name' => 'exam_level',
				'validation' => [v::stringType()->in($examLevel)->notEmpty()]
			],
			'ExamCountry' => [
				'name' => 'exam_country',
				'validation' => [v::stringType()->in($examCountry)->notEmpty()]
			],
			'Device' => [
				'name' => 'device',
				'validation' => [v::optional(v::stringType())]
			]
		];
	}
}