<?php

class PasswordComplete_Controller extends Controller
{

	public static $allowed_actions = array('index');

	public function index()
	{
		$settings = SiteConfig::current_site_config();
		$content = $settings->ForgottenPasswordConfirmationPageBody;
		return $this->renderWith('AboutPages', ['Title' => 'Password has been updated', 'Content' => $content]);
	}

}