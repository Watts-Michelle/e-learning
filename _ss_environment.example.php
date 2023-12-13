<?php

/* Change this from 'dev' to 'live' for a production environment. */
define('SS_ENVIRONMENT_TYPE', 'dev');

/* This defines a default database user */
define('SS_DATABASE_SERVER', 'localhost');
define('SS_DATABASE_USERNAME', '');
define('SS_DATABASE_PASSWORD', '');
define('SS_DATABASE_NAME', '');

global $_FILE_TO_URL_MAPPING;

$_FILE_TO_URL_MAPPING[''] = '';

define('SS_DEFAULT_ADMIN_USERNAME', '');
define('SS_DEFAULT_ADMIN_PASSWORD', '');

define('API_CLIENT_PUBLIC', '');
define('API_CLIENT_SECRET', '');

/* Local logging file path */
define('LOGGINGPATH', __DIR__.'/application_log');

/* Create a slack bot that will report errors*/
define('SLACKAPITOKEN', '');
define('SLACKAPICHANNEL', '');
define('SLACKAPIBOT', 'Monolog');

define('URBANAIRSHIP_API_URL', 'https://go.urbanairship.com/');
define('URBANAIRSHIP_API_KEY', '');
define('URBANAIRSHIP_API_SECRET', '');
define('URBANAIRSHIP_API_MASTER_SECRET', '');

define('FACEBOOKAPPID', '');