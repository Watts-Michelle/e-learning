<?php

class Information_Controller extends Base_Controller
{

	/** {@inheritdoc} */
	protected $auth = false;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'get',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'get',
	);

	public function get()
	{
		$return = [
			'page' => $this->getPages(),
			'social' => $this->getSocial(),
			'variables' => $this->getVariables(),
		];

		return (new JsonApi)->formatReturn($return);
	}

	public function getPages()
	{
		$array = [];

		foreach(Page::get()->filter(['ProvideInAPI' => 1]) as $page) {
			if(!empty($page->FrenchContent && $page->FrenchTitle)){
				$array[] = [
					'lang' => 'en',
					'name' => $page->Title,
					'safe_name' => $page->Safename,
					'content' => $this->renderWith('AboutPages', ['Title' => $page->Title, 'Content' => $page->Content])->getValue(),
					'last_updated' => strtotime($page->LastEdited)
				];
				$array[] = [
					'lang' => 'fr',
					'name' => $page->FrenchTitle,
					'safe_name' => $page->Safename,
					'content' => $this->renderWith('FrenchAboutPages', ['Title' => $page->FrenchTitle, 'Content' => $page->FrenchContent])->getValue(),
					'last_updated' => strtotime($page->LastEdited)
				];
			} else {
				$array[] = [
					'lang' => 'en',
					'name' => $page->Title,
					'safe_name' => $page->Safename,
					'content' => $this->renderWith('AboutPages', ['Title' => $page->Title, 'Content' => $page->Content])->getValue(),
					'last_updated' => strtotime($page->LastEdited)
				];
			}
		}

		return $array;
	}

	public function getSocial()
	{

		$settings = SiteConfig::current_site_config();

		return [
			'facebook' => $settings->FacebookLink,
			'twitter' => $settings->TwitterLink,
			'instagram' => $settings->InstagramLink,
			'snapchat' => $settings->SnapchatLink
		];
	}

	public function getVariables()
	{
		$variables = [
			'exam_country' => $this->getExamCountry(),
			'ethnicity' => $this->getEthnicity(),
			'user_message' => $this->getUserMessage()
		];

		return $variables;
	}

	private function getExamCountry()
	{
		$examCountries = ExamCountry::get();
		$returnArray = [];


		foreach ($examCountries as $examCountry) {
			$returnArray[] = $examCountry->getBasic();
		}

		return $returnArray;
	}

	private function getEthnicity()
	{
		$ethnicity = Ethnicity::get();
		$array = [];

		foreach ($ethnicity as $row) {
			$array[] = [
				'key' => $row->ID,
				'name' => $row->Name
			];
		}

		return $array;
	}

	private function getUserMessage()
	{
		$list = [];

		foreach(ExamLevelMessage::get() as $examLevelMessage){
			if($examLevelMessage->Message){
				$list[] = $examLevelMessage->getBasic();
			}
		}

		return $list;
	}

}