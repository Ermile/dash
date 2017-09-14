<?php
namespace lib\utility;

/** Social Network Class **/
class socialNetwork
{
	/**
	 * [twitter description]
	 * @param  [type] $_params array contain twitte params like status
	 * @param  [type] $_token  array with 5 key
	 *                         [
	 *                          ConsumerKey=>'',
	 *                          ConsumerSecret=>'',
	 *                          AccessToken=>'',
	 *                          AccessTokenSecret=>'',
	 *                          status=>''
	 *                         ]
	 * @return [type]          return status of twitte
	 */
	public static function twitter($_params, $_token = null)
	{
		require addons.'lib/SocialNetwork/codebird/codebird.php';

		if(is_array($_token) && count($_token) === 5)
		{
			// use the input array
		}
		else
		{
			// get token value from database if exist
			$qry = new \lib\dbconnection();
			$qry = $qry->query('Select `key`, `value` from options where `cat` = "twitter"');
			if($qry->num() < 5)
				return 'token';

			foreach ($qry->allassoc() as $key => $value)
				$_token[$value['key']] = $value['value'];

			// if twitter status is disable return false
			if(!isset($_token['status']))
				return 'disable';
			if($_token['status'] !== 'enable')
				return 'disable';
		}

		try
		{
			\Codebird\Codebird::setConsumerKey($_token['ConsumerKey'], $_token['ConsumerSecret']);
			$cb = \Codebird\Codebird::getInstance();
			$cb->setToken($_token['AccessToken'], $_token['AccessTokenSecret']);

			if(is_array($_params))
			{
				// user passed params as twitter parameter like below lines
				// https://github.com/jublonet/codebird-php
				// $params =
				// [
				// 		'status'    => 'I love London',
				// 		'lat'       => 51.5033,
				// 		'long'      => 0.1197,
				// 		'media_ids' => $media_ids
				// ];

			}
			else
				$_params = array('status' => $_params);

			$reply = array();
			$reply['callback'] = $cb->statuses_update($_params);
			$reply['status']  = $reply['callback']->httpstatus;
			// return $reply;
		}
		catch (\Exception $e)
		{
			$reply['callback'] = $e->getMessage();
			$reply['status']  = 'fail';
			// var_dump('Caught exception: ',  $e->getMessage(), "\n");
			// return $e->getMessage();
		}
		finally
		{

			$reply['date']  = date('Y-m-d H:i:s');
			return $reply;
		}

	}
	public static function facebook($url, $content)
	{
		$content = html_entity_decode($content);
		require_once addons.'lib/SocialNetwork/facebook-php-sdk/src/Facebook/autoload.php';
		$fb = new \Facebook\Facebook(
		[
				'app_id' => '__app_id',
				'app_secret' => '__app_secret',
				'default_graph_version' => 'v2.4',
		]);

		$linkData =
		[
			'message' => $content,
			'link' => $url
		];

		try
		{
			$response = $fb->post('/url/feed', $linkData, '__token_');
		}
		catch(\Facebook\Exceptions\FacebookResponseException $e)
		{
			return 'Graph returned an error: ' . $e->getMessage();
		}
		catch(\Facebook\Exceptions\FacebookSDKException $e)
		{
			return 'Facebook SDK returned an error: ' . $e->getMessage();
		}

		$graphNode = $response->getGraphNode();

		return $graphNode['id'];
	}

	public static function telegram($content)
	{
		// $content = html_entity_decode($content);
		// $content = preg_replace("/<\/p>/", "\n", $content);
		// $content = preg_replace("/<[^>]+>/", "", $content);
		// $content = preg_replace("/^[\s\n\r\t]+/", "", $content);
		// $robot = root."../amon-tg/public_html/send.js";
		// if(file_exists($robot)){
		// 	$ch = curl_init();
		// 	curl_setopt($ch, CURLOPT_URL, "http://127.0.0.2:8090");
		// 	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
		// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $content );
		// 	curl_exec($ch);
		// }
	}
}
