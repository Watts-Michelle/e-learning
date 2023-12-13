<?php

/**
 * Created by PhpStorm.
 * User: jonathanlittle
 * Date: 27/06/2016
 * Time: 16:06
 */
class Page extends SiteTree
{

	private static $db = array(
		'FrenchTitle' => 'Varchar(255)',
		'FrenchContent' => 'HTMLText',
		'ProvideInAPI' => 'Boolean',
		'Safename' => 'Varchar(100)'
	);

	public function getCMSFields()
	{
	    $fields = parent::getCMSFields();

		$fields->removeByName('Metadata');

		$fields->addFieldToTab('Root.FrenchTranslation', TextField::create('FrenchTitle'));
		$fields->addFieldToTab('Root.FrenchTranslation', HtmlEditorField::create('FrenchContent'));
		$fields->addFieldToTab('Root.Main', CheckboxField::create('ProvideInAPI'));
		$fields->addFieldToTab('Root.Main', TextField::create('Safename', 'Safe name for API'));

	    return $fields;
	}

}

class Page_Controller extends ContentController {

	private $showHeader = true;

	public function init() {
		parent::init();

		Requirements::css("{$this->ThemeDir()}/css/main.css");
		Requirements::css("http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css");

		Requirements::javascript("{$this->ThemeDir()}/js/main.min.js");

		if ($showHeaders = $this->request->getVar('noheader')) {
			$this->showHeader = false;
		}
	}

	public function getShowHeader()
	{
		return $this->showHeader;
	}

	public function getShowFooter()
	{
		return $this->showHeader;
	}

}