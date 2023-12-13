<?php

class LessonImporter extends CsvBulkLoader{

	public $columnMap = array(
		'DIPLOMA' => 'SubjectArea.ExamLevel.Name',
		'SYLLABUS' => 'SubjectArea.Subject.Name',
		'SECTION' => 'SubjectArea.Title',
		'TOPIC' => 'Name',
	);

	public $duplicateChecks = array('Name');

	public $columnsIgnore = array(
		'ExamLevel' => 'SubjectArea.ExamLevel.Name',
		'Subject' => 'SubjectArea.Subject.Name',
		'SubjectArea' => 'SubjectArea.Title',
	);

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

		foreach ($this->columnsIgnore as $dataObject => $field) {
			$this->relatedData[$dataObject] = $this->get_array($dataObject);
		}

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
		$class = $this->objectClass;

		// find existing object, or create new one

		//TODO rewrite this to check against the subject area
		$existingObj = Lesson::get()->filter($record)->first();

		$obj = ($existingObj) ? $existingObj : new $class();

		foreach($record as $fieldName => $val) {
			// don't bother querying if value is not set
			if($this->isNullValue($val)) continue;

			if (! in_array($fieldName, $columnMap)) {
				unset($record[$fieldName]);
				continue;
			}
			if (in_array($fieldName, $this->columnsIgnore)) continue;
		}

		// second run: save data
		foreach($record as $fieldName => $val) {
			// break out of the loop if we are previewing
			if ($preview) {
				break;
			}

			if (in_array($fieldName, $this->columnsIgnore)) continue;

			// look up the mapping to see if this needs to map to callback
			$mapped = $this->columnMap && isset($this->columnMap[$fieldName]);

			if($mapped && strpos($this->columnMap[$fieldName], '->') === 0) {
				$funcName = substr($this->columnMap[$fieldName], 2);

				$this->$funcName($obj, $val, $record);
			} else if($obj->hasMethod("import{$fieldName}")) {
				$obj->{"import{$fieldName}"}($val, $record);

			} else {
				$obj->update(array($fieldName => $val));
			}
		}

		// write record
		$id = ($preview) ? 0 : $obj->write();
		$change_count = 0;

		foreach ($this->columnsIgnore as $dataObject => $field) {

			//check if this field has been set
			if (isset($record[$field])) {
				//check if the value exists in the child object

				if ($dataObject != 'ExamLevel') {
					$record[$field] = ucfirst(strtolower($record[$field]));
				}

				$obj_id = 0;

				if ($dataObject != 'SubjectArea') {

					foreach ($this->relatedData[$dataObject] as $ID => $Name) {
						if ($Name == $record[$field]) {
							$obj_id = $ID;
							continue;
						}
					}

					//if it does not save it and store the id
					if ($obj_id == 0) {

						if (strlen($record[$field]) == 0) continue;

						$object = new $dataObject;

						$object->Name = $record[$field];
						$object->write();
						$obj_id = $object->ID;
						$this->relatedData[$dataObject][$obj_id] = $object->Name;
					}
				} else {
					$existingObject = SubjectArea::get()->filter(['Title' => $record[$field], 'SubjectID' => $obj->SubjectID, 'ExamLevelID' => $obj->ExamLevelID])->first();

					if ( ! $existingObject) {
						$object = new SubjectArea;
						$object->Title = $record[$field];
						$object->SubjectID = $obj->SubjectID;
						$object->ExamLevelID = $obj->ExamLevelID;
						$object->write();
						$obj_id = $object->ID;
					} else {
						$obj_id = $existingObject->ID;
					}
				}

				//add this to the inserted row $id and write again
				$obj->{$dataObject.'ID'} = $obj_id;

				$change_count++;
			}
		}

		if ($change_count) {
			$obj->write();
		}


		// save to results
		if($existingObj) {
			$results->addUpdated($obj, 'New lessons added');
		} else {
			$results->addCreated($obj, 'All lessons replaced');
		}

		$objID = $obj->ID;

		$obj->destroy();

		// memory usage
		unset($existingObj);
		unset($obj);

		return $objID;
	}

	private function get_array($table) {
		$rows = call_user_func(array('DataObject', 'get'), $table);

		$row_array = array();

		foreach ($rows as $row) {
			if (! empty($row->Title)) {
				$row_array[$row->ID] = $row->Title;
			} else {
				$row_array[$row->ID] = $row->Name;
			}

		}

		return $row_array;
	}

}