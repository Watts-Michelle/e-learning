<?php

class PurchaseReport extends SS_Report
{
	protected static $columns = array(
		'Date' => 'Date',
		'User' => 'User',
		'Subject' => 'Subjects',
		'Platform' => 'Platform'
	);

	public function title() {
		return 'Purchase Report';
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

		foreach(PremiumSubscription::get()->sort('Created DESC') as $orderLine) {

			$orderInfo = new DataObject(array(
				'Date' => date('d/m/Y H:i', strtotime($orderLine->Created)),
				'User' => $orderLine->Student()->Fullname,
				'Subject' => $orderLine->Subject()->Name . ' - ' . $orderLine->Subject()->ExamLevel()->Name,
				'Platform' => $orderLine->Order()->IOSSKU ? 'iOS' : 'Android'
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