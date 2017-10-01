<?php
/**
 * DevLib - Utils - Facebook Login
 * @author adrian7 (adrian@studentmoneysaver.co.uk)
 * @version 1.0
 */

namespace DevLib\API\Facebook;

use Facebook\Authentication\OAuth2Client;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookAuthorizationException;

class App{

    const FB_SDK_ERROR   = 'sdk_error';
    const FB_GRAPH_ERROR = 'graph_error';

    /**
     * @var string
     */
    protected $appId;

    /**
     * Login helper
     * @var \Facebook\Helpers\FacebookRedirectLoginHelper
     */
    protected $helper;

    /**
     * Default callback url
     * @var string
     */
    protected $callbackUrl = '/';

    /**
     * @var string|null
     */
    protected $lastFacebookError;

    /**
     * @var OAuth2Client|null
     */
    protected $oauth2Client;

    /**
     * @var GraphAccessProvider
     */
    protected $provider;

    /**
     * FB Login Constructor.
     *
     * @param $appId
     * @param $appSecret
     * @param $callbackURL
     * @param array $permissions
     * @param string $defaultGraphVersion
     */
    public function __construct(
        $appId,
        $appSecret,
        $callbackURL,
        $permissions=['id', 'email'],
        $defaultGraphVersion='v2.10'
    ) {

        $this->appId               = $appId;
        $this->callbackUrl         = $callbackURL;

        //init provider
        $this->provider = GraphAccessProvider::getInstance([
            'app_id'                => $appId,
            'app_secret'            => $appSecret,
            'default_graph_version' => $defaultGraphVersion
        ]);

        //init redirect helper
        $this->helper = $this->provider->getRedirectLoginHelper();

        //set permissions
        $this->setPermissions($permissions);

    }

    /**
     * @param $message
     * @param string $type
     */
    protected function setLastFacebookError($message, $type='generic'){

        $this->lastFacebookError = $message;

        switch ($type){

            case self::FB_GRAPH_ERROR:
                $this->lastFacebookError = ('Facebook Graph API returned an error: ' . $message);
                break;

            case self::FB_SDK_ERROR:
                $this->lastFacebookError = ( 'Facebook SDK returned an error: ' . $message);
                break;

        }
    }

    /**
     * @return GraphAccessProvider|mixed
     */
    public function getProvider(){
        return $this->provider;
    }

    /**
     * Set requested permissions
     * @param $permissions
     */
    public function setPermissions($permissions){

        if( is_array($permissions) )
            $this->provider->setPermissions($permissions);
        else
            throw new \InvalidArgumentException("Permissions should be an array... .");

    }

    /**
     * Retrieve log in url
     * @param null $callbackURL
     *
     * @return string
     */
    public function getLoginURL( $callbackURL=NULL ){

        $callbackURL = $callbackURL ?: $this->callbackUrl;

        if( $callbackURL )
            return $this->helper->getLoginUrl( $callbackURL, $this->provider->getPermissions() );

        throw new \InvalidArgumentException("Please provide a valid callback url... .");

    }

    /**
     * Redirects browser to Facebook auth page
     */
    public function redirectToFacebook(){

        $url = $this->getLoginURL();

        header("Location: {$url}", TRUE, 302);

        exit();

    }

    /**
     * Retrieve access token from callback FB request
     * @param bool $longLived
     *
     * @return \Facebook\Authentication\AccessToken|null
     * @throws FacebookAuthorizationException
     * @throws FacebookResponseException
     * @throws FacebookSDKException
     */
    public function retrieveAccessToken($longLived=TRUE){

        try {

            $accessToken = $this->helper->getAccessToken();

        } catch(FacebookResponseException $e) {
            // Graph returned an error
            $this->setLastFacebookError($e->getMessage(), self::FB_GRAPH_ERROR);
            throw $e;

        } catch(FacebookSDKException $e) {

            // When validation fails or other local issues
            $this->setLastFacebookError($e->getMessage(), self::FB_SDK_ERROR);
            throw $e;

        }

        if ( ! isset($accessToken) ) {

            //access token could not be retrieved
            if ( $error = $this->helper->getError() ) {

                $this->lastFacebookError = ( "Facebook App Error # " . $this->helper->getErrorCode() . "\n" );
                $this->lastFacebookError.= ( "Reason: " . $this->helper->getErrorReason()  . "\n" );
                $this->lastFacebookError.= ( "Description: " . $this->helper->getErrorDescription() );

            } else
                $this->lastFacebookError = ( "Facebook App Error - Unknown error while retrieving access token... . " );

            //throw auth exception
            throw new FacebookAuthorizationException( $this->lastFacebookError );

        }

        if( empty($this->oauth2Client) )
            $this->oauth2Client = $this->provider->api()->getOAuth2Client();

        //validate app id
        try{

            $metadata = $this->oauth2Client->debugToken($accessToken);
            $metadata->validateAppId( $this->appId );
            $metadata->validateExpiration();

        }
        catch (FacebookSDKException $e){
            //invalid app id
            $this->setLastFacebookError($e->getMessage(), self::FB_SDK_ERROR);
            throw $e;
        }

        if( $longLived and ! $accessToken->isLongLived() ){

            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $this->oauth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
                $this->setLastFacebookError($e->getMessage(), self::FB_SDK_ERROR);
                throw $e;
            }
        }

        return $accessToken;

    }

    /**
     * Retrieve user from Facebook callback request
     */
    public function callback(){

        $this->provider->addAccessToken( $this->retrieveAccessToken() );

        if( $this->provider->getLastAccessToken() )
            return User::getInstance( $this->provider );

    }

    /**
     * Retrieve current or last user
     *
     * @return User
     */
    public function getCurrentUser(){

        if( $this->provider->getLastAccessToken() )
            //token has been added to provider
            return User::getInstance($this->provider);

        return $this->callback();

    }

}