<?php

class MyMember_ForgotPasswordEmail extends Member_ForgotPasswordEmail
{

	protected $from = 'StudyTracks <info@studytracks.io>';  // setting a blank from address uses the site's default administrator email
	protected $subject = 'Cake';
	protected $ss_template = 'MyForgotPasswordEmail';
	private $settings;

	public function getContent()
	{
		/** @var ViewableData_Customised $template */
		$template = $this->templateData();

		$member = Member::get()->filter('Email', $template->obj('Email'))->first();

		$url = (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == 'on') ? 'https://' : 'http://';
		$url .= $_SERVER['SERVER_NAME'];

		$search = array(
			"%%FIRSTNAME%%" => $template->obj('FirstName')->getValue(),
			"%%LASTNAME%%" => $template->obj('Surname')->getValue(),
			"%%EMAIL%%" => $template->obj('Email')->getValue(),
			"%%LINK%%" => $url . $template->obj('PasswordResetLink')->getValue(),
		);

		return str_replace(array_keys($search), array_values($search), $this->settings->ForgottenPasswordBody);
	}

	public function __construct() {
		parent::__construct();

		$this->settings = SiteConfig::current_site_config();

		$this->subject = $this->settings->ForgottenPasswordSubject;
	}

}