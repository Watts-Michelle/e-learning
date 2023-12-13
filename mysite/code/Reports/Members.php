<?php

class Members extends SS_Report
{
	protected static $columns = array(
		'FirstName' => 'FirstName',
		'Surname' => 'LastName',
		'Username' => 'Username',
		'Email' => 'Email Address',
		'DateOfBirth' => 'Date Of Birth',
		'Country' => 'Country',
		'ExamCountry' => 'Exam Country',
		'ExamLevel' => 'Exam Level'
	);

	public function title() {
		return 'Members Report';
	}

	public function columns() {
		return self::$columns;
	}

	public function getColumns(){
		return self::$columns;
	}

	public function summaryFields(){
		return self::$columns;
	}

	public function sourceRecords($params, $sort, $limit) {

		$sourceRecords = new ArrayList();

		foreach(Student::get() as $Student) {

			$list = new ArrayData(array(
				'FirstName' => $Student->FirstName,
				'Surname' => $Student->Surname,
				'Username' => $Student->Username,
				'Email' => $Student->Username,
				'DateOfBirth' => $Student->DateOfBirth,
				'Country' => $Student->Country()->Name,
				'ExamCountry' => $Student->ExamCountry()->Name,
				'ExamLevel' => $Student->ExamLevel()->Name
			));

			$sourceRecords->push($list);
		}

		return $sourceRecords;
	}

	public function getReportField() {
		$gridField = parent::getReportField();
		$gridField->setModelClass('StudentsReport');
		$gridConfig = $gridField->getConfig();
		$component = $gridConfig->getComponentByType(new GridFieldPaginator());
		$component->setItemsPerPage(1000);
		return $gridField;
	}
}