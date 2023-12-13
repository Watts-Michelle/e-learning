<?php
use Vimeo\Vimeo;

class VimeoAccess
{

	private $id;
	private $secret;
	private $url;

	public function __construct($id, $secret, $url)
	{
		$this->id = $id;
		$this->secret = $secret;
		$this->url = $url;
	}

	public function authenticate()
	{
		$lib = new Vimeo($this->id, $this->secret);
		Session::set('state', base64_encode(openssl_random_pseudo_bytes(30)));
		return $lib->buildAuthorizationEndpoint($this->url, ['private', 'create', 'edit', 'upload'], Session::get('state'));
	}
}