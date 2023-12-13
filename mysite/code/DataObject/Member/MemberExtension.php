<?php

class MemberExtension extends DataExtension {

    /** @var array  Define the required fields for the User table */
    protected static $db = array(
        'Deleted' => 'Int(0)',
		'Verified' => 'Boolean',
		'VerifiedDate' => 'SS_Datetime',
		'VerificationCode' => 'Int',
		'VerificationExpiry' => 'SS_Datetime',
    );

    protected static $has_one = array(
        'Image' => 'Image',
    );

	protected static $indexes = array(
        'Email' => 'unique("Email")',
    );

    protected static $searchable_fields = array();

    protected static $summary_fields = array();

    public function updateCMSFields(FieldList $fields)
	{
		$fields->replaceField('Image', $uf = new UploadField('Image'));
		$uf->setFolderName('user/' . $this->owner->UUID);

		return $fields;
	}

	public function getFullname()
	{
		return $this->owner->FirstName . ' ' . $this->owner->Surname;
	}

	public function sendRegistrationEmail()
	{
		$member = Member::currentUser();
		$config = SiteConfig::current_site_config();

		//create registration email
		$search = array(
			"%%FIRSTNAME%%" => $this->owner->FirstName,
			"%%LASTNAME%%" => $this->owner->Surname,
			"%%EMAIL%%" => $this->owner->Email,
			"%%CODE%%" => $this->owner->VerificationCode,
			"%%CODEEXPIRY%%" => $this->owner->VerificationExpiry
		);

		$body = str_replace(array_keys($search), array_values($search), $config->RegistrationEmailBody);

		$email = new Email();

		$email
			->setFrom($config->StandardEmailFrom)
			->setTo($this->owner->Email)
			->setSubject($config->RegistrationEmailSubject)
			->setTemplate('RegistrationEmail')
			->populateTemplate(array(
				'Content' => $body
			));

		$email->send();
	}

	public function sendFrenchRegistrationEmail()
	{
		$member = Member::currentUser();
		$config = SiteConfig::current_site_config();

		//create registration email
		$search = array(
			"%%FIRSTNAME%%" => $this->owner->FirstName,
			"%%LASTNAME%%" => $this->owner->Surname,
			"%%EMAIL%%" => $this->owner->Email,
			"%%CODE%%" => $this->owner->VerificationCode,
			"%%CODEEXPIRY%%" => $this->owner->VerificationExpiry
		);

		$body = str_replace(array_keys($search), array_values($search), $config->FrenchRegistrationEmailBody);

		$email = new Email();

		$email
			->setFrom($config->FrenchStandardEmailFrom)
			->setTo($this->owner->Email)
			->setSubject($config->FrenchRegistrationEmailSubject)
			->setTemplate('RegistrationEmail')
			->populateTemplate(array(
				'Content' => $body
			));

		$email->send();
	}

	public function sendVerificationEmail()
	{
		$config = SiteConfig::current_site_config();

		//create registration email
		$search = array(
			"%%FIRSTNAME%%" => $this->owner->FirstName,
			"%%LASTNAME%%" => $this->owner->Surname,
			"%%EMAIL%%" => $this->owner->Email,
			"%%CODE%%" => $this->owner->VerificationCode,
			"%%CODEEXPIRY%%" => $this->owner->VerificationExpiry
		);

		$body = str_replace(array_keys($search), array_values($search), $config->VerificationEmailBody);

		$email = new Email();

		$email
			->setFrom($config->StandardEmailFrom)
			->setTo($this->owner->Email)
			->setSubject($config->VerificationEmailSubject)
			->setTemplate('VerificationEmail')
			->populateTemplate(array(
				'Content' => $body
			));

		$email->send();
	}

	public function sendFrenchVerificationEmail()
	{
		$config = SiteConfig::current_site_config();

		//create registration email
		$search = array(
			"%%FIRSTNAME%%" => $this->owner->FirstName,
			"%%LASTNAME%%" => $this->owner->Surname,
			"%%EMAIL%%" => $this->owner->Email,
			"%%CODE%%" => $this->owner->VerificationCode,
			"%%CODEEXPIRY%%" => $this->owner->VerificationExpiry
		);

		$body = str_replace(array_keys($search), array_values($search), $config->FrenchVerificationEmailBody);

		$email = new Email();

		$email
			->setFrom($config->FrenchStandardEmailFrom)
			->setTo($this->owner->Email)
			->setSubject($config->FrenchVerificationEmailSubject)
			->setTemplate('VerificationEmail')
			->populateTemplate(array(
				'Content' => $body
			));

		$email->send();
	}

}