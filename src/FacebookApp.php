<?php
/**
 * facebook-api - Facebook API App Facade
 * @author adrian7
 * @version 1.0
 */

namespace DevLib;

use DevLib\API\Facebook\App;
use DevLib\API\Facebook\GraphAccessProvider;
use Facebook\PersistentData\PersistentDataInterface;

class FacebookApp{

    const DEFAULT_RETURN_URL = 'https://httpbin.org/anything';

    /**
     * @var App|null
     */
    protected $app = NULL;

    /**
     * @var array
     */
    protected $params = [];

    public static function create($id, $secret){
        return new self($id, $secret);
    }

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


        //init app
        $this->app = new App($appId, $appSecret, $callback);

    }

    protected function app(){
        //TODO... app generator...
    }

    public function withCallbackURL($url){
        //TODO..
    }

    public function withLatestGraphVersion(){
        //TODO...
    }

    public function withGraphVersion($version){
        //TODO...
    }

    public function withPermissions(array $permissions){
        //TODO...
    }

    public function withPersistentDataHandler(PersistentDataInterface $handler){
        //TODO...
    }

    public function withGraphAccessProvider(GraphAccessProvider $provider){
        //TODO...
    }

    protected function getLoginURL(){
        //TODO...
    }

    protected function getUser(){
        //TODO..
    }

}