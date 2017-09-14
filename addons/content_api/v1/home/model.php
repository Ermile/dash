<?php
namespace addons\content_api\v1\home;
use \lib\utility\permission;
use \lib\utility;
use \lib\debug;
use \addons\content_enter\main\tools\token;

class model extends \mvc\model
{

	/**
	 * the user id
	 *
	 * @var        integer
	 */
	public $user_id = null;


	/**
	 * the api is telegram bot
	 *
	 * @var        boolean
	 */
	public $telegram_api_mode = false;


	/**
	 * make debug return
	 * default is true
	 * in some where in site this method is false
	 *
	 * @var        boolean
	 */
	public $debug = true;


	/**
	 * the url
	 *
	 * @var        <type>
	 */
	public $url = null;


	/**
	 * the authorization
	 *
	 * @var        <type>
	 */
	public $authorization          = null;


	/**
	 * the parent api key
	 *
	 * @var        <type>
	 */
	public $parent_api_key         = null;
	public $parent_api_key_user_id = 0;


	use tools\_use;


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_name  The name
	 * @param      <type>  $_args  The arguments
	 * @param      <type>  $parm   The parameter
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	function _call($_name, $_args, $parm)
	{
		$this->url = \lib\router::get_url();

		\lib\temp::set('api', true);

		$this->api_key();

		if(!debug::$status)
		{
			$this->_processor(['force_stop' => true]);
		}

		return parent::_call($_name, $_args, $parm);
	}


	/**
	 * check api key and set the user id
	 */
	public function api_key()
	{

		$api_token = utility::header("api_token") ? utility::header("api_token") : utility::header("Api_token");

		$authorization = utility::header("authorization");
		if($api_token)
		{
			$authorization = $api_token;
		}
		elseif(!$authorization)
		{
			$authorization = utility::header("Authorization");
		}

		if(!$authorization)
		{
			return debug::error('Authorization not found', 'authorization', 'access');
		}

		if($authorization === \lib\option::config('enter','telegram_hook'))
		{
			$this->telegram_api_mode = true;
			$this->telegram_token();
		}
		else
		{
			$token = token::get_type($authorization);

			if(!debug::$status)
			{
				return false;
			}

			switch ($token)
			{

				case 'user_token':
				case 'guest':
					if($this->url == 'v1/token/login' || $this->url == 'v1/token/guest')
					{
						debug::error(T_("Access denide (Invalid url)"), 'authorization', 'access');
						return false;
					}

					if(!token::check($authorization, $token))
					{
						return false;
					}

					$user_id = token::get_user_id($authorization);

					if(!$user_id)
					{
						debug::error(T_("Invalid authorization key (User not found)"), 'authorization', 'access');
						return false;
					}

					$this->user_id = $user_id;
					break;

				case 'api_key':
					if($this->url != 'v1/token/temp' && $this->url != 'v1/token/guest' && $this->url != 'v1/token/login')
					{
						debug::error(T_("Access denide to load this url by api key"), 'authorization', 'access');
						return false;
					}
					break;

				default :
					debug::error(T_("Invalid token"), 'authorization', 'access');
					return false;
			}

			if(isset(token::$PARENT['value']))
			{
				$this->parent_api_key = token::$PARENT['value'];
			}

			if(isset(token::$PARENT['user_id']))
			{
				$this->parent_api_key_user_id = token::$PARENT['user_id'];
			}
		}

		$this->authorization = $authorization;
	}


	/**
	 * the api telegram token
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function telegram_token()
	{
		$telegramid = utility::header("telegramid");

		if(!$telegramid)
		{
			debug::error(T_("telegramid is not set"), 'telegramid', 'header');
			return false;
		}

		$where =
		[
			'chatid' => $telegramid,
			'limit'        => 1
		];

		$user_data = \lib\db\config::public_get('users', $where);
		if(!$user_data || !isset($user_data['id']))
		{
			debug::error(T_("User not found, please register from /enter/hook"), 'telegramid', 'header');
			return false;
		}
		$this->user_id = (int) $user_data['id'];
	}


	/**
	 * save api log
	 *
	 * @param      boolean  $options  The options
	 */
	public function _processor($options = false)
	{
		// $log = [];

		// if(isset($_SERVER['REQUEST_URI']))
		// {
		// 	$log['url'] = $_SERVER['REQUEST_URI'];
		// }

		// if(isset($_SERVER['REQUEST_METHOD']))
		// {
		// 	$log['method'] = $_SERVER['REQUEST_METHOD'];
		// }

		// if(isset($_SERVER['REDIRECT_STATUS']))
		// {
		// 	$log['pagestatus'] = $_SERVER['REDIRECT_STATUS'];
		// }

		// $log['request']        = json_encode(\lib\utility::request(), JSON_UNESCAPED_UNICODE);
		// $log['debug']          = json_encode(\lib\debug::compile(), JSON_UNESCAPED_UNICODE);
		// $log['response']       = json_encode(\lib\debug::get_result(), JSON_UNESCAPED_UNICODE);
		// $log['requestheader']  = json_encode(\lib\utility::header(), JSON_UNESCAPED_UNICODE);
		// $log['responseheader'] = json_encode(apache_response_headers(), JSON_UNESCAPED_UNICODE);
		// $log['status']         = \lib\debug::$status;
		// $log['token']          = $this->authorization;
		// $log['user_id']        = $this->user_id;
		// $log['apikeyuserid']   = $this->parent_api_key_user_id;
		// $log['apikey']         = $this->parent_api_key;
		// $log['clientip']       = ClientIP;
		// $log['visit_id']       = null;

		// $log                   = \lib\utility\safe::safe($log);

		// \lib\db\apilogs::insert($log);

		parent::_processor($options);
	}
}
?>