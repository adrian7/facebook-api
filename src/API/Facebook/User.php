<?php
/**
 * DevLib - Facebook API User Class
 * @author adrian7
 * @version 1.0
 */

namespace DevLib\API\Facebook;

use Facebook\Facebook;
use Facebook\FacebookResponse;
use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Exceptions\FacebookResponseException;

/**
 * Class User
 * @package DevLib\API\Facebook
 */
class User implements \JsonSerializable {

    /**
     * @var AccessToken|null
     */
    private $accessToken;

    /**
     * @var Facebook|null
     */
    protected $api;

    /**
     * FB User id
     * @var
     */
    protected $id;

    /**
     * list of class instances
     * @var array
     */
    protected static $instances = [];

    /**
     * User constructor.
     *
     * @param GraphAccessProvider $provider
     * @param string $tokenIndex
     */
    protected function __construct(GraphAccessProvider $provider, $tokenIndex='auto') {

        $this->accessToken =
            ( 'auto' == $tokenIndex ?
                $provider->getLastAccessToken() :
                $provider->getAccessToken($tokenIndex)
            );


        $this->api      = $provider->api();

    }

    /**
     * Instance factory
     * @param GraphAccessProvider $provider
     * @param string $tokenIndex
     *
     * @return mixed
     */
    public static function getInstance(GraphAccessProvider $provider, $tokenIndex='auto'){

        $instanceId = ( 'auto' == $tokenIndex ? count(self::$instances) : intval($tokenIndex) );

        if( isset(self::$instances[$instanceId]) )
            return self::$instances[$instanceId];

        //init new instance
        self::$instances[$instanceId] = new User($provider, $tokenIndex);

        return self::$instances[$instanceId];

    }

    /**
     * Retrieve facebook user id
     * @return null|string
     * @throws FacebookResponseException
     * @throws FacebookSDKException
     * @throws \ErrorException
     */
    protected function getId(){

        if( $this->id )
            return $this->id;

        if(
            $response = $this->get('id')
                and
            $user = $response->getGraphUser()
                and
            $this->id = $user->getId()
        )
            return $this->id;

    }

    /**
     * Retrieve fields from graph path
     * @param array $fields
     * @param string $path
     * @param int $limit
     *
     * @return FacebookResponse
     * @throws FacebookResponseException
     * @throws FacebookSDKException
     * @throws \ErrorException
     */
    public function get($fields=['id', 'name', 'email'], $path='/me', $limit=0){

        $path = rtrim($path, '/');
        $path.= count($fields) ? ( '?fields=' . implode(',', $fields) ) : '' ;
        $path.= $limit ? ( '&limit=' . $limit ) : '' ;

        if( empty( $this->accessToken ) )
            throw new \ErrorException(
                "Object is missing the access token... . Please check the documentation."
            );

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $this->api->get($path, $this->accessToken);
        } catch(FacebookResponseException $e) {
            throw $e;
        } catch(FacebookSDKException $e) {
            throw $e;
        }

        //auto-fill id
        if(
            ! $this->id
                and
            ( '/me' == $path )
                and
            ( $u = $response->getGraphUser() )
                and
            ( $id = $u->getId() )
        )
            $this->id = $id;

        //return fb response
        return $response;

    }

    /**
     * Post data to graph path
     * @param $path
     * @param array $data
     *
     * @return FacebookResponse
     * @throws FacebookResponseException
     * @throws FacebookSDKException
     */
    public function post($path, array $data){

        $path = rtrim($path, '/');

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $this->api->post($path, $data, $this->accessToken);
        } catch(FacebookResponseException $e) {
            throw $e;
        } catch(FacebookSDKException $e) {
           throw $e;
        }

        return $response;

    }

    /**
     * @return string
     * @throws FacebookResponseException
     * @throws FacebookSDKException
     * @throws \ErrorException
     */
    public function __toString() {
        return ( $s = $this->getId() ) ? $s : '';
    }

    /**
     * @return array|mixed
     * @throws FacebookResponseException
     * @throws FacebookSDKException
     * @throws \ErrorException
     */
    public function jsonSerialize() {

        if( $this->accessToken )
            return [ 'id' => $this->getId() ];

        throw new \ErrorException("Cannot serialize empty object... .");

    }
}