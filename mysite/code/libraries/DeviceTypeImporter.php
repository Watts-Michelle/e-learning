<?php

class DeviceTypeImporter extends BetterBulkLoader
{
	private $deviceCampaign;

	public $columnMap = array(
		'Name' => 'Name'
	);

	/*
 * Load the given file via {@link self::processAll()} and {@link self::processRecord()}.
 * Optionally truncates (clear) the Hook and Hook_Genres tables before it imports.
 *
 * @return BulkLoader_Result See {@link self::processAll()}
 */
	public function load($filepath = null) {
		increase_time_limit_to(3600);
		increase_memory_limit_to('512M');

		if (! $filepath) {
			$filepath = $this->source->getFilePath();
		}

		$extension = File::get_file_extension($filepath);

		if ($extension == 'csv') {
			return $this->processAll($filepath);
		} else {
			return 'This file is not a CSV.';
		}
	}

	public function setDevice(DeviceCampaign $deviceCampaign)
	{
		$this->deviceCampaign = $deviceCampaign;
	}

	/**
	 * @param array $record
	 * @param array $columnMap
	 * @param BulkLoader_Result $results
	 * @param bool $preview
	 * @return mixed
	 * @throws Exception
	 */
	protected function processRecord($record, $columnMap, &$results, $preview = false) {

		if (! $this->deviceCampaign instanceof DeviceCampaign) throw new Exception('Wrong class used');

		foreach ($record as $key => $value) {
			$record[$key] = trim($value);
		}

		if (empty($record['Name'])) return false;

		$deviceType = new DeviceType();
//		$deviceType->DeviceCampaignID = $this->deviceCampaign->ID;
		$deviceType->DeviceCampaignID = 3;
		$deviceType->Name = $record['Name'];
		$deviceType->write();

		return $deviceType->ID;
	}
}