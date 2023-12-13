<?php 
class OauthRefreshToken extends DataObject {

    /** @var array  Define the required fields for the OauthRefreshToken table */
    protected static $db = array(
        'RefreshToken' => 'Varchar(225)',
        'ExpireTime' => 'SS_Datetime'
    );
    
    protected static $has_one = array(
        'OauthAccessToken' => 'OauthAccessToken'
    );
}