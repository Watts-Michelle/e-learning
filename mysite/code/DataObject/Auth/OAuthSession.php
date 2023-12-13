<?php 
class OauthSession extends DataObject {

    protected static $has_one = array(
        'Member' => 'Member'
    );

    protected static $has_many = array(
        'OauthAccessTokens' => 'OauthAccessToken'
    );
}