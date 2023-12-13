<?php

/**
 * Created by PhpStorm.
 * User: jonathanlittle
 * Date: 10/11/2016
 * Time: 14:05
 */
class MyGridFieldAddExistingAutocompleter extends GridFieldAddExistingAutocompleter
{

	/**
	 * If an object ID is set, add the object to the list
	 *
	 * @param GridField $gridField
	 * @param SS_List $dataList
	 * @return SS_List
	 */
	public function getManipulatedData(GridField $gridField, SS_List $dataList) {
		$objectID = $gridField->State->GridFieldAddRelation(null);
		if(empty($objectID)) {
			return $dataList;
		}
		$object = DataObject::get_by_id($dataList->dataclass(), $objectID);
		if($object) {
			$dataList->add($object);
		}
		$gridField->State->GridFieldAddRelation = null;

		return Controller::curr()->redirectBack();
	}

}