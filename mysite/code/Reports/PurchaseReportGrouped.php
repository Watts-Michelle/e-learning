<?php

class PurchaseReportGrouped extends SS_Report
{
	protected static $columns = array(
		'User' => 'User',
		'Purchases' => 'Purchases',
	);

	public function title() {
		return 'Purchase Report Grouped';
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

		increase_memory_limit_to('512M');
		$sourceRecords = new ArrayList;

		foreach (Student::get() as $student) {

			if (! $student->PremiumSubscription()->count()) continue;

			$purchases = '';

			foreach ($student->PremiumSubscription() as $subscription) {
				if ($purchases != '') {
					$purchases .= ', ';
				}

				$purchases .= $subscription->Subject()->Name . ' - ' . $subscription->Subject()->ExamLevel()->Name;
			}

			$orderInfo = new DataObject(array(
				'User' => $student->Fullname,
				'Purchases' => $purchases,
			));

			$sourceRecords->push($orderInfo);
		}

		return $sourceRecords;
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