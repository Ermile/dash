<?php
namespace lib\utility;

require_once addons.'lib/SocialNetwork/facebook-php-sdk/src/Facebook/autoload.php';


/** Facebook Class for managing facebook account **/
class facebook
{
	public static $token;         //Facebook tokens
	public static $fb;            //Facebook tokens

	public static function set_token()
	{
		// get token value from database if exist
		$qry = new \lib\dbconnection();
		$qry = $qry->query('Select `key`, `value` from options where `cat` = "facebook"');
		if($qry->num() < 5)
			return 'token';

		foreach ($qry->allassoc() as $key => $value)
			self::$token[$value['key']] = $value['value'];

		// if facebook status is disable return false
		if(!isset(self::$token['status']) || self::$token['status'] !== 'enable')
			return 'disable';
	}

	public static function connect()
	{
		self::set_token();

		self::$fb = new \Facebook\Facebook
		([
			'app_id'                => self::$token['app_id'],
			'app_secret'            => self::$token['app_secret'],
			'default_graph_version' => 'v2.2',
		]);
	}


	public static function login()
	{
		# login.php
		$helper = self::$fb->getRedirectLoginHelper();
		$permissions = ['email', 'user_likes']; // optional
		$loginUrl = $helper->getLoginUrl('http://archiver.dev/login-callback.php', $permissions);

		echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';


		$helper = self::$fb->getRedirectLoginHelper();
		try {
		  $accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
		  echo 'Graph returned an error: ' . $e->getMessage();
		  // exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  // exit;
		}

		if (isset($accessToken)) {
		  // Logged in!
		  $_SESSION['facebook_access_token'] = (string) $accessToken;
		  var_dump($accessToken);

		  // Now you can redirect to another page and use the
		  // access token from $_SESSION['facebook_access_token']
		}
		  var_dump($accessToken);


	}


	public static function post()
	{
		$linkData = [
		  'link' => 'http://archiver.dev',
		  'message' => 'Test',
		  ];

		try {
		  // Returns a `Facebook\FacebookResponse` object
		  $response = self::$fb->post('/me/feed', $linkData, 'token');
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  return 'Graph returned an error: ' . $e->getMessage();
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  return 'Facebook SDK returned an error: ' . $e->getMessage();

		}

		$graphNode = $response->getGraphNode();

		return 'Posted with id: ' . $graphNode['id'];
	}



	public static function send($_message)
	{
		self::connect();
		// var_dump(self::$fb);
		// self::login();
		self::post();
		// var_dump('send');

	}

}
