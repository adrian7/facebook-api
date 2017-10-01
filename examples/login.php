<?php
/**
 * DevLib - Facebook log in sample
 * @author adrian7 (adrian@studentmoneysaver.co.uk)
 * @version 1.0
 */

require_once ('../vendor/autoload.php');

//Session support is required for this demo
//see https://github.com/facebook/php-graph-sdk/blob/master/docs/reference/FacebookRedirectLoginHelper.md#login-with-facebook
session_start();

use \DevLib\API\Facebook\App;

$appId     = getenv('FACEBOOK_APP_ID');
$appSecret = getenv('FACEBOOK_APP_SECRET');

$permissions = ['email', 'user_posts'];
$callback    = ('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?callback=1');
$app         = new App($appId, $appSecret, $callback, $permissions);

if( isset($_GET['callback']) ) {

    //Refreshing the page will throw an exception, as FB SDK relies
    //on single-time use tokens to prevent CSRF

    try{

        $user = $app->getCurrentUser();
        $data = $user->get(['id', 'name', 'email'])->getGraphUser();

        //successful log in
        echo ( '<h3><i>#' . $data->getId() . '</i> ' . $data->getEmail() );

    }catch (\Facebook\Exceptions\FacebookAuthorizationException $e){
        echo ('Error: ' . $e->getMessage() );
    }

}
else{

    //display link for authentication
    echo '<h4><a href="' . $app->getLoginURL() . '">Login with facebook</a></h4>';

}