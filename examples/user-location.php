<?php
/**
 * DevLib - Facebook retrieve user location example
 * @author adrian7
 * @version 1.0
 */

require_once ('../vendor/autoload.php');

//Session support is required for this demo
//see https://github.com/facebook/php-graph-sdk/blob/master/docs/reference/FacebookRedirectLoginHelper.md#login-with-facebook
session_start();

use \DevLib\API\Facebook\App;

$appId     = getenv('FACEBOOK_APP_ID');
$appSecret = getenv('FACEBOOK_APP_SECRET');

$permissions = [
    'email',
    'public_profile',
    'user_location',
    'user_birthday'
];

$callback    = ('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?callback=1');

//create app
$app = App::create($appId, $appSecret)
          ->withPermissions($permissions)
          ->withCallbackURL($callback);

if( isset($_GET['callback']) ) {

    //Refreshing the page will throw an exception, as FB SDK relies
    //on single-time use tokens to prevent CSRF

    try{

        $user = $app->getUser();
        $data = $user->get([
            'id',
            'name',
            'email',
            'location',
            'address'
        ])->getGraphUser();

        //get country and approx address
        if( $location = $data->getLocation() ){

            $details = $user->get(['location'], $location->getId())->getGraphPage();
            $location= $details->getLocation();

            $address = (
                $location->getStreet() . ' ' .
                $location->getCity()   . ' ' .
                $location->getZip()    . ' ' .
                $location->getCountry()
            );

            $address = str_replace('  ', ' ', $captured['address']);

            $latitude = $location->getLatitude();
            $longitude= $location->getLongitude();

           $country = $location->getCountry();

        }

        //successful log in
        echo ( '<h3><i>#' . $data->getId() . '</i> ' . $data->getName() . '</h3> ' );
        echo ( '<h4>' . $address );
        echo ( '<h4>' . $country );

    }catch (\Facebook\Exceptions\FacebookSDKException $e){
        echo ( 'Error: ' . $e->getMessage() );
    }

}
else{

    //display link for authentication
    echo '<h4><a href="' . $app->getLoginURL() . '">Login with facebook</a></h4>';

}