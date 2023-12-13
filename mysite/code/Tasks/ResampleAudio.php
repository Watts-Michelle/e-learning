<?php

class ResampleAudio_Task extends Controller
{

	public function init() {

		parent::init();

		if (php_sapi_name() != "cli") {
			if (!Member::currentUser()) {

				return Security::PermissionFailure($this->controller, 'You must be logged in to access this page');
			}

			if (!Member::currentUser()->inGroups(array('administrators'))) {
				return Security::PermissionFailure($this->controller, 'You must be logged in as admin to access this page');
			}
		}
	}

	public static $allowed_actions = array('index');

	public function index()
	{
		set_time_limit(0);
		foreach(Lesson::get() as $lesson) {
			if ($lesson->MediaFileID && $lesson->Type == 'audio') {
				try {
					$mp3 = new MP3($lesson->MediaFile());
					$mp3->resample(false);
				} catch (Exception $e) {
					echo 'Not MP3';
				}
			}
		}
		echo date('Y-m-d H:i:s') . ': Audio resampled';
	}

}