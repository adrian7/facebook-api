<?php
/**
 * facebook-api - Facebook API App Facade
 * @author adrian7
 * @version 1.0
 */

namespace DevLib\API\Facebook;

use Facebook\PersistentData\PersistentDataInterface;

class App{

    const DEFAULT_RETURN_URL = 'https://httpbin.org/anything';

    /**
     * @var Instance|null
     */
    protected $app = NULL;

    /**
     * Cache app params hash
     * @var string|null
     */
    protected $version = NULL;

    /**
     * @var array
     */
    protected $params = [
        'appId'                 => NULL,
        'appSecret'             => NULL,
        'callbackURL'           => NULL,
        'permissions'           => [],
        'persistentDataHandler' => NULL,
        'defaultGraphVersion'   => 'latest'
    ];

    /**
     * Create new Facebook App
     *
     * @param $id
     * @param $secret
     *
     * @return App
     */
    public static function create($id, $secret){
        return new self($id, $secret);
    }

    /**
     * FacebookApp constructor.
     *
     * @param $appId
     * @param $appSecret
     */
    protected function __construct($appId, $appSecret) {

        //determine a default callback url
        if( isset($_SERVER) and isset($_SERVER['HTTP_HOST']) )

            $callback = (
                $_SERVER['SERVER_PROTOCOL'] . '://' .
                $_SERVER['HTTP_HOST'] .
                $_SERVER['REQUEST_URI']
            );

        else

            $callback = self::DEFAULT_RETURN_URL;


        //init params
        $this->addParams([
            'appId'         => $appId,
            'appSecret'     => $appSecret,
            'callbackURL'   => $callback
        ]);

    }

    /**
     * Add parameters
     * @param $key
     * @param null $value
     *
     * @return $this
     */
    protected function addParams($key, $value=NULL){

        if( is_string($key) ){

            // key=>value parameter
            if( isset($this->params[$key]) ){

                $this->params[$key]     = $value;
                $this->params['hash']   = time(); //TODO find better hash stamp

            }
            else
                throw new \InvalidArgumentException("Unsupported parameter {$key}... ");

        }
        else if( is_array($key) )
            // map
            foreach ($key as $k=>$v)
                $this->addParams($k, $v);

        else if( is_object($key) and $a = get_object_vars($key) )
            foreach ($a as $k=>$v)
                $this->addParams($k, $v);

        //return object
        return $this;

    }

    /**
     * App object generator
     * @return Instance|null
     */
    protected function app(){

        if ($this->version != $this->params['hash'] )
            //create app object
            $this->app = new Instance(
                $this->params['appId'],
                $this->params['appSecret'],
                $this->params['callbackURL'],
                $this->params['permissions'],
                $this->params['persistentDataHandler'],
                $this->params['defaultGraphVersion']
            );

        //update version
        $this->version = $this->params['hash'];

        //serve app
        return $this->app;

    }

    /**
     * Set callback url
     *
     * @param $url
     *
     * @return App
     */
    public function withCallbackURL($url){
        return $this->addParams('callbackURL', $url);
    }

    /**
     * Set default Graph API version to latest
     * @return App
     */
    public function withLatestGraphVersion(){
        return $this->addParams('defaultGraphVersion', 'latest');
    }

    /**
     * Set custom default graph version
     *
     * @param $version
     *
     * @return App
     */
    public function withGraphVersion($version){
        return $this->addParams('defaultGraphVersion', $version);
    }

    /**
     * Set app permissions
     *
     * @param array $permissions
     *
     * @return App
     */
    public function withPermissions(array $permissions){
        return $this->addParams('permissions', $permissions);
    }

    /**
     * Set persistent data handler
     *
     * @param PersistentDataInterface $handler
     *
     * @return App
     */
    public function withPersistentDataHandler(PersistentDataInterface $handler){
        return $this->addParams('persistentDataHandler', $handler);
    }

    /**
     * Retrieve log in url
     * @return string
     */
    public function getLoginURL(){
        return $this->app()->getLoginURL();
    }

    /**
     * Retrieve current user
     * @return User
     */
    public function getUser(){
        return $this->app()->getCurrentUser();
    }

}