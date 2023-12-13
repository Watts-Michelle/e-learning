<?php

class SettingsConfig extends DataExtension implements PermissionProvider {

	private static $db = array(
		'DefaultQuestionCount' => 'Int',
		'GoogleAnalytics' => 'Varchar(20)',
		'PointsCompletedQuiz' => 'Int',
		'PointsCompletedLesson' => 'Int',
		'PointsCompletedSubject' => 'Int',
		'PointsCompletedSubjectArea' => 'Int',
		'PurchasePushText' => 'Varchar(200)',
		'VimeoToken' => 'Varchar(255)',
		'MinimumProvidedVideoHeight' => 'Int',
		'IOSAppDownloadLink' => 'Varchar(255)',
		'AndroidAppDownloadLink' => 'Varchar(255)',
		'SchoolRegistrationEmailFrom' => 'Varchar(255)',
		'SchoolRegistrationEmailSubject' => 'Varchar(100)',
		'SchoolRegistrationEmailBody' => 'HTMLText',

		'FrenchSchoolRegistrationEmailFrom' => 'Varchar(255)',
		'FrenchSchoolRegistrationEmailSubject' => 'Varchar(100)',
		'FrenchSchoolRegistrationEmailBody' => 'HTMLText',

		'StandardEmailFrom' => 'Varchar(255)',

		'FrenchStandardEmailFrom' => 'Varchar(255)',

		'ForgottenPasswordSubject' => 'Varchar(255)',
		'ForgottenPasswordBody' => 'HTMLText',
		'ForgottenPasswordConfirmationPageBody' => 'HTMLText',

		'FrenchForgottenPasswordSubject' => 'Varchar(255)',
		'FrenchForgottenPasswordBody' => 'HTMLText',
//		'FrenchForgottenPasswordConfirmationPageBody' => 'HTMLText',

		'FacebookLink' => 'Varchar(255)',
		'InstagramLink' => 'Varchar(255)',
		'TwitterLink' => 'Varchar(255)',
		'SnapchatLink' => 'Varchar(255)',
		'DoublePoints' => 'Boolean',
		'ConcurrentLogins' => 'Int',
		'AllowPointsForMultipleListens' => 'Boolean',
		'DefaultQuizTime' => 'Int(360)',
		'RegistrationEmailSubject' => 'Varchar(100)',
		'RegistrationEmailBody' => 'HTMLText',

		'FrenchRegistrationEmailSubject' => 'Varchar(100)',
		'FrenchRegistrationEmailBody' => 'HTMLText',

		'VerificationEmailSubject' => 'Varchar(100)',
		'VerificationEmailBody' => 'HTMLText',

		'FrenchVerificationEmailSubject' => 'Varchar(100)',
		'FrenchVerificationEmailBody' => 'HTMLText',

		'VimeoTeacherHelpVideo' => 'Varchar(255)',
		'VimeoStudentHelpVideo' => 'Varchar(255)',

		'SchoolStudentCap' => 'Int',
		'SchoolStaffCap' => 'Int',

		'FreeSubscriptionExpirationDate' => 'SS_DateTime'
	);

	public function providePermissions() {
		return array(
			'APPCONFIG_CAN_VIEW' => array(
				'name' => 'Can view settings',
				'category' => 'Settings',
				'help' => 'Can view the current application settings',
				'sort' => 3
			),
			'APPCONFIG_CAN_EDIT' => array(
				'name' => 'Can edit settings',
				'category' => 'Settings',
				'help' => 'Can edit the current application settings',
				'sort' => 4
			),
		);
	}

	protected static $has_one = array(
		'MenuLogo' => 'Image',
		'StudentImportTemplate' => 'File'
	);

	protected static $has_many = array(
		'FooterLinks' => 'FooterLink',
		'PointQuizBrackets' => 'PointQuizBracket'
	);

	public function updateCMSFields(FieldList $fields) {

		$fields->addFieldsToTab('Root.Main', array (
			UploadField::create('MenuLogo'),
			TextField::create('GoogleAnalytics'),
			TextField::create('DefaultQuestionCount'),
			NumericField::create('DefaultQuizTime', 'Default time for quiz (seconds)'),
			NumericField::create('MinimumProvidedVideoHeight'),
			TextField::create('AppDownloadLink'),
			TextField::create('IOSAppDownloadLink'),
			TextField::create('AndroidAppDownloadLink'),
			TextField::create('FacebookLink'),
			TextField::create('InstagramLink'),
			TextField::create('TwitterLink'),
			TextField::create('SnapchatLink'),
			TextField::create('PurchasePushText'),
			NumericField::create('ConcurrentLogins'),
		));

		$fields->addFieldsToTab('Root.Points', array(
			CheckboxField::create('AllowPointsForMultipleListens', 'Allow points for multiple listens'),
			NumericField::create('PointsCompletedQuiz', 'Points for completing a quiz'),
			NumericField::create('PointsCompletedLesson', 'Points for completing a lesson'),
			NumericField::create('PointsCompletedSubjectArea', 'Points for completing a subject area'),
			NumericField::create('PointsCompletedSubject', 'Points for completing a subject'),
			$gf = new GridField('PointQuizBrackets', 'Quiz Point Results', PointQuizBracket::get(), $conf = new GridFieldConfig_RecordEditor()),
			CheckboxField::create('DoublePoints', 'Award Double Points'),
		));

		$fields->addFieldsToTab('Root.PointLevels', array(
			$gf = new GridField('PointLevels', 'Point Levels', PointLevel::get(), $conf = new GridFieldConfig_RecordEditor()),
		));

		$fields->addFieldsToTab('Root.SchoolEmail', array(
			EmailField::create('SchoolRegistrationEmailFrom'),
			TextField::create('SchoolRegistrationEmailSubject'),
			HtmlEditorField::create('SchoolRegistrationEmailBody'),
			LabelField::create('conf_message_tags', "Usable tags: <br>%%FIRSTNAME%% - The user's first name<br>%%LASTNAME%% - The user's surname<br>%%EMAIL%% - The user's email address<br>%%PASSWORD%% - The user's password<br>%%IOSAPPDOWNLOADLINK%% - Link to download the iOS app<br>%%ANDROIDAPPDOWNLOADLINK%% - Link to download the Android app")
		));

		$fields->addFieldsToTab('Root.FrenchSchoolEmail', array(
			EmailField::create('FrenchSchoolRegistrationEmailFrom'),
			TextField::create('FrenchSchoolRegistrationEmailSubject'),
			HtmlEditorField::create('FrenchSchoolRegistrationEmailBody'),
			LabelField::create('conf_message_tags', "Usable tags: <br>%%FIRSTNAME%% - The user's first name<br>%%LASTNAME%% - The user's surname<br>%%EMAIL%% - The user's email address<br>%%PASSWORD%% - The user's password<br>%%IOSAPPDOWNLOADLINK%% - Link to download the iOS app<br>%%ANDROIDAPPDOWNLOADLINK%% - Link to download the Android app")
		));

		$fields->addFieldsToTab('Root.AppEmails', array(
			EmailField::create('StandardEmailFrom'),
			TextField::create('RegistrationEmailSubject'),
			HtmlEditorField::create('RegistrationEmailBody'),
			LabelField::create('conf_messagea_tags', "Usable tags: <br>%%FIRSTNAME%% - The user's first name<br>%%LASTNAME%% - The user's surname<br>%%EMAIL%% - The user's email address<br>%%PASSWORD%% - The user's password<br>%%CODE%% - Code to input in the app<br>%%CODEEXPIRY%% - When the code expires<hr>"),
			TextField::create('VerificationEmailSubject'),
			HtmlEditorField::create('VerificationEmailBody'),
			LabelField::create('conf_messages_tags', "Usable tags: <br>%%FIRSTNAME%% - The user's first name<br>%%LASTNAME%% - The user's surname<br>%%EMAIL%% - The user's email address<br>%%PASSWORD%% - The user's password<br>%%CODE%% - Code to input in the app<br>%%CODEEXPIRY%% - When the code expires<hr>"),
			TextField::create('ForgottenPasswordSubject'),
			HtmlEditorField::create('ForgottenPasswordBody'),
			LabelField::create('conf_messagela_tags', "Usable tags: <br>%%FIRSTNAME%% - The user's first name<br>%%LASTNAME%% - The user's surname<br>%%EMAIL%% - The user's email address<br>%%PASSWORD%% - The user's password<br>%%LINK%% - Forgotten password link<br><hr>"),
			HtmlEditorField::create('ForgottenPasswordConfirmationPageBody')
		));

		$fields->addFieldsToTab('Root.FrenchAppEmails', array(
			EmailField::create('FrenchStandardEmailFrom'),
			TextField::create('FrenchRegistrationEmailSubject'),
			HtmlEditorField::create('FrenchRegistrationEmailBody'),
			LabelField::create('conf_messagea_tags', "Usable tags: <br>%%FIRSTNAME%% - The user's first name<br>%%LASTNAME%% - The user's surname<br>%%EMAIL%% - The user's email address<br>%%PASSWORD%% - The user's password<br>%%CODE%% - Code to input in the app<br>%%CODEEXPIRY%% - When the code expires<hr>"),
			TextField::create('FrenchVerificationEmailSubject'),
			HtmlEditorField::create('FrenchVerificationEmailBody'),
			LabelField::create('conf_messages_tags', "Usable tags: <br>%%FIRSTNAME%% - The user's first name<br>%%LASTNAME%% - The user's surname<br>%%EMAIL%% - The user's email address<br>%%PASSWORD%% - The user's password<br>%%CODE%% - Code to input in the app<br>%%CODEEXPIRY%% - When the code expires<hr>"),
			TextField::create('FrenchForgottenPasswordSubject'),
			HtmlEditorField::create('FrenchForgottenPasswordBody'),
			LabelField::create('conf_messagela_tags', "Usable tags: <br>%%FIRSTNAME%% - The user's first name<br>%%LASTNAME%% - The user's surname<br>%%EMAIL%% - The user's email address<br>%%PASSWORD%% - The user's password<br>%%LINK%% - Forgotten password link<br><hr>"),
//			HtmlEditorField::create('FrenchForgottenPasswordConfirmationPageBody')
		));

		$fields->addFieldsToTab('Root.HelpVideos', array(
			TextField::create('VimeoTeacherHelpVideo'),
			TextField::create('VimeoStudentHelpVideo'),
			UploadField::create('StudentImportTemplate')->setAllowedExtensions('CSV')
		));

		$fields->addFieldsToTab('Root.SchoolDefaults', array(
			TextField::create('SchoolStudentCap'),
			TextField::create('SchoolStaffCap'),
		));

		$fields->addFieldsToTab('Root.Subscriptions', array(
			DatetimeField::create('FreeSubscriptionExpirationDate'),
		));

	}

	/**
	 * Get the actions that are sent to the CMS. In
	 * your extensions: updateEditFormActions($actions)
	 *
	 * @return Fieldset
	 */
	public function getCMSActions() {
		if (Permission::check('ADMIN') || Permission::check('EDIT_ADDCONFIG')) {
			$actions = new FieldList(
				FormAction::create('save_appconfig', _t('CMSMain.SAVE','Save'))
					->addExtraClass('ss-ui-action-constructive')->setAttribute('data-icon', 'accept')
			);
		} else {
			$actions = new FieldList();
		}

		return $actions;
	}

	public function canView($member = null) {
		return Permission::check("APPCONFIG_CAN_VIEW");
	}

	public function canEdit($member = null) {
		return Permission::check("APPCONFIG_CAN_EDIT");
	}

	public function canDelete($member = null) {
		return false;
	}

	public function canCreate($member = null) {
		return false;
	}

	/*
	 * Fetch the current app config record - there should only ever be one
	 */
	public static function current_app_config() {
		if ($appConfig = DataObject::get_one('SettingsConfig')) return $appConfig;

		return self::make_app_config();
	}

	/*
	 * Function to create an initial AppConfig record when one doesn't exist
	 */
	private static function make_app_config() {
		$appConfig = new SettingsConfig();
		$appConfig->write;
		return $appConfig;
	}


}