<?php 
class WestOauthAccessToken extends DataObject {

    /** @var array  Define the required fields for the OauthAccessToken table */
    protected static $db = array(
        'AccessToken' => 'Varchar(255)',
        'ExpireTime' => 'SS_Datetime'
    );
}