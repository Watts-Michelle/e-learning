<?php 
class OauthAccessToken extends DataObject {

    /** @var array  Define the required fields for the OauthAccessToken table */
    protected static $db = array(
        'AccessToken' => 'Varchar(255)',
        'ExpireTime' => 'SS_Datetime'
    );
    
    protected static $has_one = array(
        'OauthSession' => 'OauthSession',
    );

    protected static $has_many = array(
        'OauthRefreshTokens' => 'OauthRefreshToken'
    );
}