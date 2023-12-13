<?php

global $project;
$project = 'mysite';

global $database;

// Use _ss_environment.php file for configuration
require_once("conf/ConfigureFromEnv.php");
require_once '../vendor/autoload.php';

$Validator = new PasswordValidator();
$Validator->minLength(8);
$Validator->checkHistoricalPasswords(2);
$Validator->characterStrength(2, array('lowercase', 'uppercase', 'digits'));
Member::set_password_validator($Validator);

LeftAndMain::require_css('mysite/css/leftandmainextracss.css');

Object::add_extension('SiteConfig', 'SettingsConfig');
CMSMenu::remove_menu_item('Help');

$stream = new \Monolog\Handler\StreamHandler(LOGGINGPATH . date('Y-m-d').'.log', \Monolog\Logger::INFO);
$fc = new \Monolog\Handler\FingersCrossedHandler($stream, null, 50);

$apiLogger = new \Monolog\Logger('api');
$apiLogger->pushHandler($fc);

$generalLogger = new \Monolog\Logger('general');
$generalLogger->pushHandler($fc);

if (SLACKAPITOKEN && SLACKAPICHANNEL && SLACKAPIBOT) {
	$extra = new \Monolog\Handler\SlackHandler(SLACKAPITOKEN, SLACKAPICHANNEL, SLACKAPIBOT);
	$apiLogger->pushHandler($extra);
	$generalLogger->pushHandler($extra);
}

\Monolog\Registry::addLogger($apiLogger);
\Monolog\Registry::addLogger($generalLogger);

if (Member::currentUserID()) {
	$logger = \Monolog\Registry::getInstance('general');
	$logger->addInfo('member', ['ID' => Member::currentUserID()]);
}

Object::useCustomClass('MemberLoginForm', 'SchoolLoginForm');
Object::useCustomClass('Member_ForgotPasswordEmail', 'MyMember_ForgotPasswordEmail');
Object::useCustomClass('Member_ForgotPasswordFrenchEmail', 'MyMember_ForgotPasswordEmail');
Object::useCustomClass('ChangePasswordForm', 'MyChangePasswordForm');

if (empty($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/') {
	echo 'working';
	exit;
}

SS_Report::add_excluded_reports(['BrokenFilesReport', 'BrokenLinksReport', 'BrokenRedirectorPagesReport', 'BrokenVirtualPagesReport', 'RecentlyEditedReport', 'EmptyPagesReport']);

if (Director::isLive()) {
	Email::set_mailer(new PostmarkMailer());
}