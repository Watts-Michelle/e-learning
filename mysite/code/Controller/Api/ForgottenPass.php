<?php

class ForgottenPass_Controller extends Base_Controller
{

	/** {@inheritdoc} */
	protected $auth = false;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'forgot'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'forgot',
	);

	public function forgot()
	{
		$body = new ForgottenPasswordForm($this->requestBody);
		$data = $body->process();

		$login = new SchoolLoginForm(new Security(), 'Security');
		$login->forgotPassword(['Email' => $data['Email']]);

		return (new JsonApi)->formatReturn([]);
	}
}