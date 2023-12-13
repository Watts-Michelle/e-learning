<?php

class Vimeo_Controller extends Controller
{

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'callback',
		'newToken',
		'upload',
		'getFileURLs'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'new//$dummy' => 'newToken',
		'files//$dummy' => 'getFileURLs',
		'upload//$dummy' => 'upload',
		'' => 'callback',
	);

	private $vimeo;

	public function init()
	{
		parent::init();

		if (php_sapi_name() != "cli") {
			if (!Member::currentUser()) {

				return Security::PermissionFailure($this->controller, 'You must be logged in to access this page');
			}

			if (!Member::currentUser()->inGroups(array('administrators'))) {
				return Security::PermissionFailure($this->controller, 'You must be logged in as admin to access this page');
			}
		}

		$this->vimeo = new Vimeo\Vimeo(VIMEO_ID, VIMEO_SECRET);
	}

	public function newToken(SS_HTTPRequest $request)
	{
		$callback = "http://" . $_SERVER['SERVER_NAME'] . '/vimeo';
		$auth = new VimeoAccess(VIMEO_ID, VIMEO_SECRET, $callback);
		return $this->redirect($auth->authenticate());
	}

	public function callback()
	{
		// Callback url, respond to the information sent from vimeo and turn that into a usable access token
		if (Session::get('state') != $_GET['state']) {
			echo 'Something is wrong. Vimeo sent back a different state than this script was expecting. Please let vimeo know that this has happened';
		}

		$tokens = $this->vimeo->accessToken($_GET['code'], "http://" . $_SERVER['SERVER_NAME'] . '/vimeo');

		if ($tokens['status'] == 200) {
			$config = SiteConfig::current_site_config();
			$config->VimeoToken = $tokens['body']['access_token'];
			$config->write();
			$this->redirect('/');
		} else {
			echo "Unsuccessful authentication";
			var_dump($tokens);
		}
	}

	public function upload()
	{

		$config = SiteConfig::current_site_config();

		if (! $config->VimeoToken) throw new Exception('No vimeo access token');

		// Upload Default Media

		$lessons = Lesson::get()->filter(['Type' => 'video', 'Uploaded' => 0, 'MediaFileID:GreaterThan' => 0]);

		foreach ($lessons as $lesson) {

			if ($lesson->MediaLink) continue;

			$uploader = new VimeoUpload(VIMEO_ID, VIMEO_SECRET, $config->VimeoToken, $lesson);

			$uploadedFile = $uploader->upload();

			if ($uploadedFile) {
				$vimeo = new VimeoFile;
				$vimeo->setIDsFromURI($uploadedFile['body']['uri']);
				$vimeo->LessonID = $lesson->ID;
				$vimeo->write();

				$lesson->VimeoFileID = $vimeo->ID;
				$lesson->Uploaded = 1;
				$lesson->write();
			}
		}

		// Upload Alternative Media

		$lessons = Lesson::get()->filter(['AltType' => 'video', 'AltUploaded' => 0, 'AltMediaFileID:GreaterThan' => 0]);

		foreach ($lessons as $lesson) {

			if ($lesson->AltMediaLink) continue;

			$uploader = new VimeoUploadAlt(VIMEO_ID, VIMEO_SECRET, $config->VimeoToken, $lesson);

			$uploadedFile = $uploader->upload();

			if ($uploadedFile) {
				$vimeo = new AltVimeoFile;
				$vimeo->setIDsFromURI($uploadedFile['body']['uri']);
				$vimeo->LessonID = $lesson->ID;
				$vimeo->write();

				$lesson->AltVimeoFileID = $vimeo->ID;
				$lesson->AltUploaded = 1;
				$lesson->write();
			}
		}

		echo date('Y-m-d H:i:s') . ': uploaded';
	}

	public function getFileURLs()
	{
		$config = SiteConfig::current_site_config();

		if (! $config->VimeoToken) throw new Exception('No vimeo access token');

		$vimeoFiles = VimeoFile::get();

		foreach ($vimeoFiles as $vimeoFile) {
			if($vimeoFile->ClassName == 'VimeoFile') {
				if ($vimeoFile->VimeoFileURL()->Count() < 5) {
					$vimeo = new VimeoUpload(VIMEO_ID, VIMEO_SECRET, $config->VimeoToken, $vimeoFile->Lesson());
					$files = $vimeo->getFiles();

					if (!empty($files)) {
						foreach ($files as $file) {
							// Only insert this URL if we don't alreay have it
							if(!$vimeoFile->VimeoFileURL(array('Quality' => $file['quality']))->Count()) {
								$vimeoFileURL = new VimeoFileURL();
								$vimeoFileURL->Quality = $file['quality'];
								$vimeoFileURL->Height = $file['height'];
								$vimeoFileURL->Width = $file['width'];
								$vimeoFileURL->Link = $file['link'];
								$vimeoFileURL->Size = $file['size'];
								$vimeoFileURL->write();
								$vimeoFile->VimeoFileURL()->add($vimeoFileURL);
							}
						}
					}
				}
			} else {
				if ($vimeoFile->VimeoFileURL()->Count() < 5) {
					$vimeo = new VimeoUploadAlt(VIMEO_ID, VIMEO_SECRET, $config->VimeoToken, $vimeoFile->Lesson());
					$files = $vimeo->getFiles();

					if (!empty($files)) {
						foreach ($files as $file) {
							// Only insert this URL if we don't already have it
							if(!$vimeoFile->VimeoFileURL(array('Quality' => $file['quality']))->Count()) {
								$vimeoFileURL = new VimeoFileURL();
								$vimeoFileURL->Quality = $file['quality'];
								$vimeoFileURL->Height = $file['height'];
								$vimeoFileURL->Width = $file['width'];
								$vimeoFileURL->Link = $file['link'];
								$vimeoFileURL->Size = $file['size'];
								$vimeoFileURL->write();
								$vimeoFile->VimeoFileURL()->add($vimeoFileURL);
							}
						}
					}
				}
			}
		}

		echo date('Y-m-d H:i:s') . ': Vimeo files stored.';
	}

}