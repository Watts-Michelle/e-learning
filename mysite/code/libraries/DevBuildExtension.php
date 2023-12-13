<?php

/**
 * Hook into dev/build and add extra functionality
 *
 * @package StudyTracks
 * @subpackage Libraries
 * @author Jonathan Little <jonathan@flipsidegroup.com>
 */
class DevBuildExtension extends DevBuildController {

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'build'
	);

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'build'
	);

	/**
	 * Ensure there is a row in SettingsConfig and build auth tables
	 *
	 * Hook into the existing dev build process
	 * Settings Config needs a row to function properly, this will make sure it exists
	 *
	 * @param $request
	 */
	public function build($request) {

		parent::build($request);

		if (! SiteConfig::current_site_config()) {
			$settings = new SettingsConfig;
			$settings->write();
		}

		/** @var Student $student */
		//$student = new Student;
		//$student->generateStudents();

		/** @var Staff $staff */
		//$staff = new Staff;
		//$staff->generateStaff();

	}

}
