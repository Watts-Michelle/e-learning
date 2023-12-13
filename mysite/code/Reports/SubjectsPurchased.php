<?php

class SubjectsPurchased extends SS_Report
{
	protected static $columns = array(
		'Subject' => 'Subject',
		'ExamLevel' => 'Exam Level',
		'Purchases' => 'Purchases',
	);

	public function title() {
		return 'Subjects Purchased';
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

		foreach(Subject::get() as $subject) {
			$count = $subject->PremiumSubscription()->count();

			$orderInfo = new ArrayData(array(
				'Subject' => $subject->Name,
				'ExamLevel' => $subject->ExamLevel()->Name,
				'Purchases' => $count,
			));

			$sourceRecords->push($orderInfo);
		}

		return $sourceRecords->sort('Purchased');
	}

	public function getReportField() {
		$gridField = parent::getReportField();
		$gridField->setModelClass('PurchaseReport');
		$gridConfig = $gridField->getConfig();
		$component = $gridConfig->getComponentByType(new GridFieldPaginator());
		$component->setItemsPerPage(1000);
		return $gridField;
	}

}