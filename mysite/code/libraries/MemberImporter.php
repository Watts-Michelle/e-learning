<?php

class MemberImporter extends CsvBulkLoader{

	public $columnMap = array(
		'email' => 'Email',
		'name' => 'FirstName',
	);

	public $duplicateChecks = array('Email');
	public $relatedData = array();

	/*
 * Load the given file via {@link self::processAll()} and {@link self::processRecord()}.
 * Optionally truncates (clear) the Hook and Hook_Genres tables before it imports.
 *
 * @return BulkLoader_Result See {@link self::processAll()}
 */
	public function load($filepath) {
		increase_time_limit_to(3600);
		increase_memory_limit_to('512M');

		return $this->processAll($filepath);
	}

	/**
	 *
	 * @param array $record
	 * @param array $columnMap
	 * @param BulkLoader_Result $results
	 * @param boolean $preview
	 *
	 * @return int
	 */
	protected function processRecord($record, $columnMap, &$results, $preview = false) {

		foreach ($record as $key => $value) {
			$record[$key] = trim($value);
		}

		if (empty($record['Email'])) return false;
		if (Member::get()->filter(['Email' => $record['Email']])->first()) return false;

		if (! EmailField::create('Email', 'Email')->setValue($record['Email'])->validate(new RequiredFields)) return false;

		$member = new Student;
		$member->Email = $record['Email'];
		$member->CountryID = 236;
		$member->ExamLevelID = 1;
		$member->ExamCountryID = 1;

		if (! empty($record['FirstName'])) {
			$name = explode(' ', $record['FirstName']);
			$member->FirstName = $name[0];

			if (isset($name[1])) {
				unset($name[0]);
				$member->Surname = implode(' ', $name);
			}
		}

		$member->write();

		return 0;
	}

}