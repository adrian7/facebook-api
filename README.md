# Devlib/Facebook-API
Simple Facebook API wrapper for PHP

### Install
`composer require devlib/facebook-api`

### Usage
```php
use \DevLib\API\Facebook\App;

$appId     = getenv('FACEBOOK_APP_ID');
$appSecret = getenv('FACEBOOK_APP_SECRET');

$permissions = ['email', 'user_posts'];
$callback    = ('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?callback=1');

$app         = new App($appId, $appSecret, $callback, $permissions);
```

### Generate login url
```php
//display link for authentication
echo '<a href="' . $app->getLoginURL() . '">Login with Facebook</a>';
```

### Retrieve user info
```php
try{

    $user = $app->getCurrentUser();
    $data = $user->get(['id', 'name', 'email'])->getGraphUser();

    //successful log in
    echo ( '<h3><i>#' . $data->getId() . '</i> ' . $data->getEmail() );

}
catch (\Facebook\Exceptions\FacebookAuthorizationException $e){
    echo ('Error: ' . $e->getMessage() );
}
```

### Facebook API Reference:
 - [Permissions Reference - Facebook Login](https://developers.facebook.com/docs/facebook-login/permissions)
 - [Graph API Reference](https://developers.facebook.com/docs/graph-api/reference/)

