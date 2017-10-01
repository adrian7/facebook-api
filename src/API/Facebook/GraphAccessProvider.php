<?php
/**
 * DevLib - Facebook API Graph Access Provider Class
 * @author adrian7
 * @version 1.0
 */

namespace DevLib\API\Facebook;

use \Facebook\Facebook;
use \Facebook\Authentication\AccessToken;

class GraphAccessProvider{

    /**
     * @var Facebook
     */
    protected $wrapper;

    /**
     * @var array
     */
    protected $accessTokens = [];

    /**
     * @var AccessToken
     */
    protected $lastAccessToken;

    /**
     * @var array
     */
    protected $permissions;

    /**
     * App instances
     * @var array
     */
    protected static $instances = [];

    /**
     * Configuration parameters
     * @var array
     */
    protected $params = [];

    /**
     * Configuration defaults
     * @var array
     */
    protected $defaultParams = [
        'default_graph_version'          => Facebook::DEFAULT_GRAPH_VERSION,
        'enable_beta_mode'               => FALSE,
        'http_client_handler'            => NULL,
        'persistent_data_handler'        => NULL,
        'pseudo_random_string_generator' => NULL,
        'url_detection_handler'          => NULL
    ];

    /**
     * GraphAccessProvider constructor.
     *
     * @param Facebook $facebook
     */
    protected function __construct(Facebook $facebook) {
        $this->wrapper = $facebook;
    }

    /**
     * Retrieve a graph access provider instance
     * @param array $params
     *
     * @return GraphAccessProvider
     */
    public static function getInstance(array $params){

        if( ! isset($params['app_id']) or ! isset($params['app_secret']) )
            throw new \InvalidArgumentException("Missing app_id or app_secret from input params... .");

        $instanceId = substr(strval($params['app_id']), 0, 7);

        if( ! isset( self::$instances[$instanceId] ) ){

            self::$instances[$instanceId] = new GraphAccessProvider(
                new Facebook($params)
            );

        }

        return self::$instances[$instanceId];

    }

    /**
     * Facebook object wrapper
     * @return Facebook
     */
    public function api(){
        return $this->wrapper;
    }

    /**
     * Retrieve FB redirect login helper
     * @return \Facebook\Helpers\FacebookRedirectLoginHelper
     */
    public function getRedirectLoginHelper(){
        return $this->wrapper->getRedirectLoginHelper();
    }

    /**
     * @param array $permissions
     */
    public function setPermissions(array $permissions=[]){
        $this->permissions = $permissions;
    }

    /**
     * @return array
     */
    public function getPermissions(){
        return $this->permissions;
    }

    /**
     * Check if the provider has access tokens
     * @return bool
     */
    public function hasAccessTokens(){
        return count($this->accessTokens) > 0;
    }

    /**
     * @param AccessToken $accessToken
     *
     * @return int index of the added access token
     */
    public function addAccessToken(AccessToken $accessToken){

        $this->accessTokens[] = $accessToken;

        return count($this->accessTokens) -1;

    }

    /**
     * @param int $index
     *
     * @return mixed
     */
    public function getAccessToken($index=0){
        return $this->accessTokens[$index];
    }

    /**
     * @return AccessToken|null
     */
    public function getLastAccessToken(){

        if( count($this->accessTokens) )
            return $this->accessTokens[ count($this->accessTokens) -1 ];

        return NULL;

    }

}