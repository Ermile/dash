<?php
namespace dash\utility;

class enter
{
	public static $life_time_code = 60 * 5;


	public static function try($_module)
	{
		self::set_session($_module, intval(self::get_session($_module)) + 1);

		$count_try = intval(self::get_session($_module));

		switch ($_module)
		{
			case 'enter_invalid_force_code':
				if($count_try >= 1)
				{
					\dash\log::set('userBanedIn_'. $_module);
					// ban user for 2 min
					\dash\session::set('enter_baned_user', true, null, (60 * 2));
				}
				break;
			case 'invalid_password':
			case 'verify_invalid_code':
			case 'diffrent_mobile':
			case 'login_user_not_found':
			case 'login_status_block':
			case 'browser_pass_saved_invalid':
			case 'change_pass_invalid_old_pass':
			case 'pass_invalid_pass':
			case 'pass_recovery_pass_not_set':
			case 'pass_set_password_not_set':
			case 'pass_signup_password_not_set':
			case 'username_username_not_set':
			case 'change_pass_have_not_pass':
				if($count_try > 10)
				{
					\dash\log::set('userBanedIn_'. $_module);
					// ban user for 2 min
					\dash\session::set('enter_baned_user', true, null, (60 * 2));
				}
				break;

			default:
				# code...
				break;
		}
	}


	public static function clean_session($_low = false)
	{
		if($_low)
		{
			$alert = [];

			if(isset($_SESSION['enter']['alert']))
			{
				$alert = $_SESSION['enter']['alert'];
			}

			unset($_SESSION['enter']);

			$_SESSION['enter']['alert'] = $alert;
		}
		else
		{
			unset($_SESSION['enter']);
			\dash\session::clean_cat('enter');
		}
	}


	public static function set_session($_key, $_value, $_sub_key = null)
	{
		if(!isset($_SESSION['enter']))
		{
			$_SESSION['enter'] = [];
		}

		if($_sub_key)
		{
			if(!isset($_SESSION['enter'][$_key]))
			{
				$_SESSION['enter'][$_key] = [];
			}

			$_SESSION['enter'][$_key][$_sub_key] = $_value;
		}
		else
		{
			$_SESSION['enter'][$_key] = $_value;
		}
	}


	public static function get_session($_key, $_sub_key = null)
	{
		if($_sub_key)
		{
			if(isset($_SESSION['enter'][$_key][$_sub_key]))
			{
				return $_SESSION['enter'][$_key][$_sub_key];
			}
		}
		else
		{
			if(isset($_SESSION['enter'][$_key]))
			{
				return $_SESSION['enter'][$_key];
			}
		}
		return null;
	}


	// user in \lib\log to set log of this user
	// this user not login yet but detail is loaded
	public static function user_id()
	{
		return self::user_data('id');
	}


	/**
	 * Loads an user data.
	 */
	public static function load_user_data($_user_aut_key, $_type = 'mobile')
	{
		$data = [];

		if(!$_user_aut_key)
		{
			return [];
		}

		switch ($_type)
		{
			case 'usernameormobile':
				$data = \dash\db\users::find_user_to_login($_user_aut_key);
				break;

			case 'mobile':
				if(\dash\utility\filter::mobile($_user_aut_key))
				{
					$data = \dash\db\users::get_by_mobile($_user_aut_key);
				}
				break;

			case 'username':
				if(preg_match("/^[A-Za-z0-9\_\-]+$/", $_user_aut_key) && preg_match("/[A-Za-z]+/", $_user_aut_key))
				{
					$data = \dash\db\users::get_by_username($_user_aut_key);
				}
				break;

			case 'user_id':
				if(ctype_digit($_user_aut_key) && is_numeric($_user_aut_key))
				{
					$data = \dash\db\users::get_by_id($_user_aut_key);
				}
				break;

			case 'email':
				if(filter_var($_user_aut_key, FILTER_VALIDATE_EMAIL))
				{
					$data = \dash\db\users::get_by_email($_user_aut_key);
				}
				break;

			case 'loaded':
				$data = $_user_aut_key;
				break;

		}

		if($data)
		{
			self::set_session('user_data', $data);
		}
		return $data;
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_key   The key
	 */
	public static function user_data($_key = null)
	{
		if(!isset($_SESSION['enter']['user_data']))
		{
			self::load_user_data(self::get_session('usernameormobile'), 'usernameormobile');
		}

		if($_key)
		{
			if(isset($_SESSION['enter']['user_data'][$_key]))
			{
				return $_SESSION['enter']['user_data'][$_key];
			}
			return null;
		}

		if(isset($_SESSION['enter']['user_data']))
		{
			return $_SESSION['enter']['user_data'];
		}
		return null;
	}


	/**
	*	Signup new user
	*/
	public static function signup($_args = [])
	{

		$default_args =
		[
			'mobile'      => null,
			'displayname' => null,
			'password'    => null,
			'email'       => null,
			'status'      => 'awaiting'
		];

		if(is_array($_args))
		{
			$_args = array_merge($default_args, $_args);
		}

		// save ref in users table
		if(isset($_SESSION['ref']) && !isset($_args['ref']))
		{
			$_args['ref'] = intval($_SESSION['ref']);
			unset($_SESSION['ref']);
		}

		$mobile = self::get_session('mobile');
		if($mobile)
		{
			// set mobile to use in other function
			$_value          = $mobile;
			$_args['mobile'] = $mobile;
			$_args['email']  = $_value;

			$user_id = \dash\db\users::signup($_args);

			if($user_id)
			{
				self::load_user_data($user_id, 'user_id');
			}
			else
			{
				\dash\log::set('enterCanNotSignupInDb');
				return false;
			}

			self::set_session('first_signup', true);

			return $user_id;
		}
		else
		{
			\dash\log::set('enterCanNotSignup');
			return false;
		}
	}


	/**
	*	Signup new user
	*/
	public static function signup_email($_args = [])
	{
		if(self::get_session('dont_will_set_mobile'))
		{
			// $_args['dontwillsetmobile'] = date("Y-m-d H:i:s");
		}
		else
		{
			if(self::get_session('temp_mobile') && !isset($_args['mobile']))
			{
				$_args['mobile'] = self::get_session('temp_mobile');
			}
		}

		self::set_session('first_signup', true);

		// save ref in users table
		if(isset($_SESSION['ref']) && !isset($_args['ref']))
		{
			$_args['ref'] = intval($_SESSION['ref']);
			unset($_SESSION['ref']);
		}

		$user_id = \dash\db\users::signup($_args);

		if($user_id)
		{
			$_value = \dash\db::insert_id();
			self::load_user_data($_value, 'user_id');
		}
		return $_value;

	}

	/**
	 * find redirect url
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function find_redirect_url($_url = null)
	{
		if($_url)
		{
			return $_url;
		}

		$host = \dash\url::kingdom();

		// get url language
		// if have referer redirect to referer
		if(\dash\request::get('referer'))
		{
			$host = \dash\request::get('referer');
		}
		elseif(isset($_SESSION['enter_referer']) && $_SESSION['enter_referer'])
		{
			$host = $_SESSION['enter_referer'];
			unset($_SESSION['enter_referer']);
			// need another place
			$_SESSION['lastEnterReferer'] = $host;
		}
		elseif(self::get_session('app_mode'))
		{
			$host .= '/enter/app';
			self::set_session('app_mode', false);
		}
		elseif(self::get_session('first_signup'))
		{
			// if first signup
			if(\dash\option::config('enter', 'singup_redirect'))
			{
				$host .= '/'. \dash\option::config('enter', 'singup_redirect');
			}
			else
			{
				$host .= \dash\option::config('redirect');
			}
		}
		else
		{
			$host .='/'. \dash\option::config('redirect');
		}

		return $host;
	}



	/**
	 * login
	 */
	public static function enter_set_login($_url = null, $_auto_redirect = false)
	{
		if(\dash\user::id())
		{
			$user_id = \dash\user::id();
		}
		else
		{
			$user_id = self::user_data('id');
		}

		if(!$user_id)
		{
			\dash\log::set('loginNoUserIdWasFounded');
			\dash\notif::warn(T_("User id not found to save your session"));
			return;
		}


		if(self::user_data('twostep'))
		{
			if(isset($_SESSION['main_account']) && isset($_SESSION['main_mobile']) && self::user_data('mobile') )
			{
				if(self::user_data('mobile') === $_SESSION['main_mobile'])
				{
					if(self::get_session('twostep_is_ok'))
					{
						// no problem
					}
					else
					{
						// set session verify_from set
						\dash\utility\enter::set_session('verify_from', 'ask_twostep');

						// send code way
						\dash\utility\enter::go_to_verify();
					}
				}
				else
				{
					// if the admin user login by this user
					// not ask two-step
				}
			}
			else
			{
				if(self::get_session('twostep_is_ok'))
				{
					// no problem
				}
				else
				{
					// set session verify_from set
					\dash\utility\enter::set_session('verify_from', 'ask_twostep');

					// send code way
					\dash\utility\enter::go_to_verify();
				}
			}
		}

		\dash\user::init($user_id);

		$set_session = false;

		if(is_null(self::user_data('forceremember')))
		{
			// default of this variable is true
			if(\dash\option::config('enter', 'remember_me'))
			{
				$set_session = true;
			}
			else
			{
				// no login by remember
				$set_session = false;
			}
		}
		elseif(is_numeric(self::user_data('forceremember')))
		{
			if(intval(self::user_data('forceremember')) === 0)
			{
				$set_session = false;
				// no login by remember
			}
			elseif(intval(self::user_data('forceremember')) === 1)
			{
				$set_session = true;

			}
		}

		if(isset($_SESSION['main_account']) && isset($_SESSION['main_mobile']))
		{
			if(isset($_SESSION['auth']['mobile']) && $_SESSION['auth']['mobile'] === $_SESSION['main_mobile'])
			{
				// nothign
				// read $set_session
			}
			else
			{
				// not save session for other people never
				$set_session = false;
			}
		}
		else
		{
			// check user status
			// if the user status is awaiting
			// set the user status as enable
			if(self::user_data('status') === 'awaiting' && is_numeric($user_id))
			{
				\dash\db\users::update(['status' => 'active'], $user_id);
			}
		}

		if($set_session)
		{
			// set remeber and save session
			\dash\db\sessions::set($user_id);
		}

		$url = self::find_redirect_url($_url);

		// clean session
		self::clean_session();

		if($_auto_redirect)
		{

			\dash\notif::direct(true);
			// go to new address
			\dash\redirect::to($url);
		}
		else
		{
			self::set_session('redirect_url', $url);
			return $url;
		}

	}


	/**
	 * Sets the logout.
	 *
	 * @param      <type>  $_user_id  The user identifier
	 */
	public static function set_logout($_user_id, $_auto_redirect = true)
	{

		if($_user_id && is_numeric($_user_id))
		{
			if(isset($_SESSION['main_account']) && isset($_SESSION['main_mobile']) && isset($_SESSION['auth']['mobile']))
			{
				if($_SESSION['auth']['mobile'] === $_SESSION['main_mobile'])
				{
					\dash\db\sessions::logout($_user_id);
				}
				// if the admin user login by this user
				// not save the session
			}
			else
			{
				// set this session as logout
				\dash\db\sessions::logout($_user_id);
			}
		}

		/**
		 * destroy user id
		 */
		\dash\user::destroy();

		$_SESSION = [];

		// unset and destroy session then regenerate it
		session_unset();

		if(session_status() === PHP_SESSION_ACTIVE)
		{
			session_destroy();
		}

		if($_auto_redirect)
		{

			if(\dash\request::get('referer') || \dash\request::get('mobile'))
			{
				$get_enter_query = [];
				if(\dash\request::get('mobile'))
				{
					$get_enter_query['mobile']   = \dash\request::get('mobile');
					$get_enter_query['autosend'] = 1;
				}

				if(\dash\request::get('referer'))
				{
					$get_enter_query['referer']   = \dash\request::get('referer');
				}

				\dash\redirect::to(\dash\url::kingdom(). '/enter?'. http_build_query($get_enter_query));
			}
			else
			{
				// go to base
				self::go_to('main');
			}
		}

	}


	public static function load_chat_id($_id)
	{
		$get =
		[
			'user_id' => $_id,
			'limit'   => 1,
		];

		$result = \dash\db\user_telegram::get($get);

		if(isset($result['chatid']))
		{
			return $result['chatid'];
		}

		return null;
	}


	/**
	 * return list of way we can send code to the user
	 *
	 * @param      <type>  $_mobile_or_email  The usernameormobile
	 *
	 * @return     array   ( description_of_the_return_value )
	 */
	public static function list_send_code_way($_mobile_or_email = null)
	{
		$mobile    = null;
		$chatid    = null;
		$email     = null;

		if($_mobile_or_email)
		{
			$mobile = $_mobile_or_email;
			$email  = $_mobile_or_email;
		}
		elseif(self::user_data('mobile') || self::user_data('email'))
		{
			// check detail from user data: new user loaded data in this session
			$mobile = self::user_data('mobile');
			$email  = self::user_data('email');
			if(self::user_data('id'))
			{
				$chatid = self::load_chat_id(self::user_data('id'));
			}
		}
		elseif(\dash\user::detail('mobile') || \dash\user::detail('email'))
		{
			// check detail from user session: user logined and data is saved in session
			$mobile = \dash\user::detail('mobile');
			$email  = \dash\user::detail('email');
			if(\dash\user::id())
			{
				$chatid = self::load_chat_id(\dash\user::id());
			}
		}
		elseif(self::get_session('signup_detail'))
		{
			$signup_detail = self::get_session('signup_detail');
			if(isset($signup_detail['mobile']))
			{
				$mobile = $signup_detail['mobile'];
			}
		}

		$way = [];

		if($email)
		{
			// load email way
			// array_push($way, 'email');
		}

		if($chatid && \dash\option::social('telegram', 'status'))
		{
			if(\dash\option::config('enter', 'verify_telegram'))
			{
				array_push($way, 'telegram');
			}
		}

		if($mobile && \dash\utility\filter::mobile($mobile))
		{
			if(\dash\option::config('enter', 'verify_sms'))
			{
				array_push($way, 'sms');
			}

			if(\dash\option::config('enter', 'verify_call'))
			{
				array_push($way, 'call');
			}

			if(\dash\option::config('enter', 'verify_sendsms'))
			{
				array_push($way, 'sendsms');
			}
		}

		if(\dash\url::isLocal() && empty($way))
		{
			array_push($way, 'sms');
		}

		if(\dash\utility\enter::get_session('verify_from') === 'signup')
		{
			if(!\dash\option::config('enter', 'force_verify'))
			{
				array_push($way, 'later');
			}
		}

		if(empty($way))
		{
			self::next_step('verify/what');
			self::open_lock('verify/what');
			self::open_lock('verify');
			self::go_to('verify/what');
		}

		return $way;
	}


	/**
	 * Sends a code way.
	 */
	public static function go_to_verify()
	{
		$host = \dash\url::kingdom();
		$host .= '/enter/verify';
		self::open_lock('verify');
		\dash\redirect::to($host);
	}



	/**
	 * generate verification code
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function create_new_code($_way = null)
	{
		$code =  rand(10000,99999);

		if(\dash\url::isLocal())
		{
			$code = 11111;
		}

		// set verification code in session
		self::set_session('verification_code', $code);
		$time = date("Y-m-d H:i:s");

		$user_id = self::user_data('id');
		if(!$user_id && \dash\user::id())
		{
			$user_id = \dash\user::id();
		}

		$log_detail =
		[
			'to'     => $user_id,
			'send'   => 1,
			'notif'  => 1,
			'code'   => $code,
			'mycode' => $code,
			'time'   => $time,
		];

		if(in_array(self::get_session('verify_from'), ['two_step_set', 'two_step_unset', 'password_change']))
		{
			$log_detail['secret'] = true;
		}


		$log_id = \dash\log::set('enter_VerificationCode', $log_detail);

		// save this code in logs table and session
		// $log_id = \dash\db\logs::set('user:verification:code', self::user_data('id'), $log_meta);

		self::set_session('verification_code', $code);
		self::set_session('verification_code_time', $time);
		self::set_session('verification_code_way', $_way);
		self::set_session('verification_code_id', $log_id);

		return $code;
	}


	/**
	 * check code exist and live
	 */
	public static function generate_verification_code()
	{
		// check last code time and if is not okay make new code
		$last_code_ok = false;
		// get saved session last verification code

		if
		(
			self::get_session('verification_code') &&
			self::get_session('verification_code_id') &&
			self::get_session('verification_code_time')
		)
		{
			if(time() - strtotime(self::get_session('verification_code_time')) < self::$life_time_code)
			{
				// last code is true
				// need less to create new code
				$last_code_ok = true;
			}
		}


		// user code not found
		if(!$last_code_ok)
		{
			if(self::user_data('id'))
			{
				// 'enable','disable','expire','deliver','awaiting','deleted','cancel','block','notif','notifread','notifexpire'
				$where =
				[
					'caller' => 'enter_VerificationCode',
					'to'     => self::user_data('id'),
					'status' => ["IN", "('enable', 'notif', 'notifread')"],
					'limit'  => 1,
				];
				$log_code = \dash\db\logs::get($where);

				if($log_code)
				{
					if(isset($log_code['datecreated']) && time() - strtotime($log_code['datecreated']) < self::$life_time_code)
					{
						// the last code is okay
						// need less to create new code
						$last_code_ok = true;
						// save data in session
						if(isset($log_code['code']))
						{
							self::set_session('verification_code', $log_code['code']);
						}
						// save log time
						if(isset($log_code['datecreated']))
						{
							self::set_session('verification_code_time', $log_code['datecreated']);
						}

						// save log id
						if(isset($log_code['id']))
						{
							self::set_session('verification_code_id', $log_code['id']);
						}

					}
					else
					{
						// the log is exist and the time of log is die
						// we expire the log
						if(isset($log_code['id']))
						{
							\dash\db\logs::update(['status' => 'expire'], $log_code['id']);
						}
					}
				}
			}
		}
		// if last code is not okay
		// make new code
		if(!$last_code_ok)
		{
			self::create_new_code();
		}
	}



	public static function check_code($_module)
	{
		$log_meta =
		[
			'meta' =>
			[
				'session' => $_SESSION,
				'post'    => \dash\request::post(),
			]
		];

		$verificationCode = \dash\request::post('code');

		if(!$verificationCode)
		{
			\dash\notif::error(T_("Please fill the verification code"), 'code');
			return false;
		}

		$verificationCode = \dash\utility\convert::to_en_number($verificationCode);

		if(!is_numeric($verificationCode))
		{
			\dash\log::set('codeStringGivenMustNumber');
			\dash\notif::error(T_("What happend? the code is number. you try to send string!?"), 'code');
			return false;
		}

		$code_is_okay = false;
		// if the module is sendsms user not send the verification code here
		// the user send the verification code to my sms service
		// and this code is deffirent by verification code
		if($_module === 'sendsms')
		{
			$code = $verificationCode;
			if($code == self::get_session('sendsms_code'))
			{
				$log_id = self::get_session('sendsms_code_log_id');

				if($log_id)
				{
					$get_log_detail = \dash\db\logs::get(['id' => $log_id, 'limit' => 1]);
					if(!$get_log_detail || !isset($get_log_detail['status']))
					{
						\dash\db\logs::set('enter:verify:sendsmsm:log:not:found', self::user_data('id'), $log_meta);
						\dash\notif::error(T_("System error, try again"));
						return false;
					}

					switch ($get_log_detail['status'])
					{
						case 'deliver':
							// the user must be login
							\dash\db\logs::update(['status' => 'expire'], $log_id);
							$code_is_okay = true;
							// set login session
							// $redirect_url = self::enter_set_login();

							// // save redirect url in session to get from okay page
							// self::set_session('redirect_url', $redirect_url);
							// // set okay as next step
							// self::next_step('okay');
							// // go to okay page
							// self::go_to('okay');
							break;

						case 'enable':
							// user not send sms or not deliver to us
							\dash\db\logs::set('enter:verify:sendsmsm:sms:not:deliver:to:us', self::user_data('id'), $log_meta);
							\dash\notif::error(T_("Your sms not deliver to us!"));
							return false;
							break;

						case 'expire':
							// the user user from this way and can not use this way again
							// this is a bug!
							\dash\db\logs::set('enter:verify:sendsmsm:sms:expire:log:bug', self::user_data('id'), $log_meta);
							\dash\notif::error(T_("What are you doing?"));
							return false;
						default:
							// bug!
							return false;
							break;
					}
				}
				else
				{
					\dash\db\logs::set('enter:verify:sendsmsm:log:id:not:found', self::user_data('id'), $log_meta);
					\dash\notif::error(T_("What are you doing?"));
					return false;
				}
			}
			else
			{
				\dash\db\logs::set('enter:verify:sendsmsm:user:inspected:change:html', self::user_data('id'), $log_meta);
				\dash\notif::error(T_("What are you doing?"));
				return false;
			}
		}
		else
		{
			if(intval($verificationCode) === intval(self::get_session('verification_code')))
			{
				$code_is_okay = true;
			}
		}

		if(!$code_is_okay)
		{
			// wrong code sleep code
			\dash\log::set('invalidCode');
			self::try('verify_invalid_code');
			\dash\code::sleep(3);
			\dash\notif::error(T_("Wrong code entered, Please enter the 5 digit code sent to your mobile phone"), 'code');
			return false;
		}


		// expire code
		if(self::get_session('verification_code_id'))
		{
			// the user enter the code and the code is ok
			// must expire this code
			\dash\db\logs::update(['status' => 'expire'], self::get_session('verification_code_id'));
			self::set_session('verification_code', null);
			self::set_session('verification_code_time', null);
			self::set_session('verification_code_way', null);
			self::set_session('verification_code_id', null);
		}

		// to no check again
		self::set_session('twostep_is_ok', true);


		if(is_numeric(self::user_data('id')) && !self::user_data('verifymobile'))
		{
			\dash\db\users::update(['verifymobile' => 1], self::user_data('id'));
		}

		if(is_numeric(\dash\user::id()) && !\dash\user::detail('verifymobile'))
		{
			\dash\db\users::update(['verifymobile' => 1], \dash\user::id());
		}

		/**
		 ***********************************************************
		 * VERIFY FROM
		 * PASS/SIGNUP
		 * PASS/SET
		 * PASS/RECOVERY
		 ***********************************************************
		 */
		if(
			(
				self::get_session('verify_from') === 'signup' ||
				self::get_session('verify_from') === 'set' ||
				self::get_session('verify_from') === 'recovery'
			) &&
			self::get_session('temp_ramz_hash') &&
			is_numeric(self::user_data('id'))
		  )
		{
			\dash\log::set('PasswordOK');
			// set temp ramz in use pass
			\dash\db\users::update(['password' => self::get_session('temp_ramz_hash')], self::user_data('id'));
		}


		/**
		 ***********************************************************
		 * VERIFY FROM
		 * USERNAME
		 * TRY TO REMOVE USER NAME
		 ***********************************************************
		 */
		if(self::get_session('verify_from') === 'username_remove' && is_numeric(self::user_data('id')))
		{
			// set temp ramz in use pass
			\dash\db\users::update(['username' => null], self::user_data('id'));
			// remove usename from sessions
			unset($_SESSION['auth']['username']);
			// set the alert message
			$alert =
			[
				'clean_session' => true,
				'text' => T_("Your username was removed"),
				'link' => \dash\url::kingdom(),
			];

			\dash\log::set('usernameRemoved');

			self::set_session('alert', $alert);
			// open lock of alert page
			self::next_step('alert');

			// self::clean_session(true);
			// go to alert page
			self::go_to('alert');
			return;
		}

		/**
		 ***********************************************************
		 * VERIFY FROM
		 * ENTER/DELETE
		 * DELETE ACCOUNT
		 ***********************************************************
		 */
		if(self::get_session('verify_from') === 'delete')
		{
			if(self::get_session('why'))
			{
				$update_meta  = [];

				$meta = \dash\user::detail('meta');
				if(!$meta)
				{
					$update_meta['why'] = self::get_session('why');
				}
				elseif(is_string($meta) && substr($meta, 0, 1) !== '{')
				{
					$update_meta['other'] = $meta;
					$update_meta['why'] = self::get_session('why');
				}
				elseif(is_string($meta) && substr($meta, 0, 1) === '{')
				{
					$json = json_decode($meta, true);
					$update_meta = array_merge($json, ['why' => self::get_session('why')]);
				}

			}

			$update_user = [];
			if(!empty($update_meta))
			{
				$update_user['meta'] = json_encode($update_meta, JSON_UNESCAPED_UNICODE);
			}
			$update_user['status'] = 'removed';

			\dash\db\users::update($update_user, \dash\user::id());

			\dash\log::set('userDeletedOK');

			\dash\db\sessions::delete_account(\dash\user::id());

			self::next_step('byebye');
			// self::clean_session(true);
			self::go_to('byebye');
			return;
		}

		/**
		 ***********************************************************
		 * VERIFY FROM
		 * USERNAME/SET
		 * USERNAME/CHANGE
		 ***********************************************************
		 */
		if(
			(
				self::get_session('verify_from') === 'username_set' ||
				self::get_session('verify_from') === 'username_change'
			) &&
			self::get_session('temp_username') &&
			is_numeric(self::user_data('id'))
		  )
		{
			// set temp ramz in use pass
			\dash\db\users::update(['username' => self::get_session('temp_username')], self::user_data('id'));
			// set the alert message
			if(self::get_session('verify_from') === 'username_set')
			{
				\dash\log::set('usernameSetOK');
				$text = T_("Your username was set");
			}
			else
			{
				\dash\log::set('usernameChangeOK');
				$text = T_("Your username was change");
			}


			if(isset($_SESSION['auth']) && is_array($_SESSION['auth']))
			{
				$_SESSION['auth']['username'] = self::get_session('temp_username');
			}

			// set the alert message
			$alert =
			[
				'clean_session' => true,
				'text'          => $text,
				'link'          => \dash\url::kingdom(),
			];

			self::set_session('alert', $alert);
			// open lock of alert page
			self::next_step('alert');
			// self::clean_session(true);
			// go to alert page
			self::go_to('alert');
			return;
		}

		/**
		 ***********************************************************
		 * VERIFY FROM
		 * EMAIL/SET
		 * EMAIL/CHANGE
		 ***********************************************************
		 */
		if(
			(
				self::get_session('verify_from') === 'email_set' ||
				self::get_session('verify_from') === 'email_change'
			) &&
			self::get_session('temp_email') &&
			is_numeric(self::user_data('id'))
		  )
		{
			\dash\log::set('emailOK');

			// set temp ramz in use pass
			\dash\db\users::update(['email' => self::get_session('temp_email')], self::user_data('id'));
		}



		/**
		 ***********************************************************
		 * VERIFY FROM
		 * TWO STEP VERICICATION SET
		 ***********************************************************
		 */
		if(self::get_session('verify_from') === 'two_step_set' &&	is_numeric(self::user_data('id')))
		{
			// set on two_step of this user
			\dash\db\users::update(['twostep' => 1], self::user_data('id'));
			$alert =
			[
				'clean_session' => true,
				'text' => T_("Your two-step verification is now active for your account"),
				'link' => \dash\url::kingdom(). '/account/security',
			];

			\dash\log::set('twoStepActive');
			\dash\user::refresh();
			self::set_session('alert', $alert);
			self::next_step('alert');
			self::go_to('alert');
			return;
		}


		/**
		 ***********************************************************
		 * VERIFY FROM
		 * TWO STEP VERICICATION SET
		 ***********************************************************
		 */
		if(self::get_session('verify_from') === 'two_step_unset' &&	is_numeric(self::user_data('id')))
		{
			// set off two_step of this user
			\dash\db\users::update(['twostep' => 0], self::user_data('id'));
			$alert =
			[
				'clean_session' => true,
				'text' => T_("Your two-step verification is now deactive for your account"),
				'link' => \dash\url::kingdom(). '/account/security',
			];

			\dash\log::set('twoStepDeactive');
			\dash\user::refresh();
			self::set_session('alert', $alert);
			self::next_step('alert');
			self::go_to('alert');
			return;
		}


		if(self::get_session('verify_from') === 'password_change' && \dash\user::id() && self::get_session('temp_ramz_hash'))
		{

			// set off two_step of this user
			\dash\db\users::update(['password' => self::get_session('temp_ramz_hash')], \dash\user::id());
			\dash\db\sessions::change_password(\dash\user::id());
			\dash\user::refresh();
			$alert =
			[
				'clean_session' => true,
				'text'          => T_("Your password was changed"),
				'link'          => \dash\url::kingdom(). '/account/security',
			];

			\dash\log::set('passwordChangeOK');

			self::set_session('alert', $alert);
			// open lock of alert page
			self::next_step('alert');
			// self::clean_session(true);
			// go to alert page
			self::go_to('alert');
			return;

		}

		// set login session
		$redirect_url = self::enter_set_login();

		// save redirect url in session to get from okay page
		self::set_session('redirect_url', $redirect_url);
		// set okay as next step
		self::next_step('okay');
		// go to okay page
		self::go_to('okay');
	}


	/**
	 * Sends a code email.
	 * send verification code whit email address
	 */
	public static function send_code_email()
	{
		$email = self::get_session('temp_email');
		$code  = self::generate_verification_code();
		$mail =
		[
			'to'      => $email,
			'subject' => 'contact',
			'body'    => "salam". $code,
		];
		// $mail = \dash\mail::send($mail);
		return $mail;
	}



	/**
	 * lock or unlock step
	 * and check is lock or not lock
	 *
	 * @param      <type>  $_module  The module
	 * @param      <type>  $_acction     The set
	 */
	public static function lock($_module, $_acction = null)
	{
		if($_acction === true)
		{
			self::set_session('lock', true, $_module);
		}

		if($_acction === false)
		{
			self::set_session('lock', false, $_module);
		}

		if($_acction === null)
		{
			// in dev and when we in debug mode
			// we have not any page to lock!
			if(\dash\url::isLocal())
			{
				// return false;
			}
			// get lock from session
			$is_lock = self::get_session('lock', $_module);
			// if is lock or not set
			if($is_lock === true || $is_lock === null)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}


	/**
	 * Opens a lock.
	 *
	 * @param      <type>  $_module  The module
	 */
	public static function open_lock($_module)
	{
		self::lock($_module, false);
	}


	/**
	 * disable all lock page and set only this module is true
	 *
	 * @param      <type>  $_module  The module
	 */
	public static function next_step($_module)
	{
		// unset all other lock
		unset($_SESSION['enter']['lock']);
		// set jusn this lock
		self::set_session('lock', false, $_module);
	}


	/**
	 * redirect to url
	 *
	 * @param      <type>  $_url   The url
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function go_to($_url = null)
	{
		$host = \dash\url::kingdom();

		if($_url === 'okay')
		{
			\dash\notif::ok(null, ['unlock' => false]);
			\dash\notif::redirect($host. '/enter/okay');
			return;
		}

		$url  = null;
		switch ($_url)
		{
			// redirect to base
			case 'base':
				$url = $host. '/enter';
				break;

			case 'main':
				$url = $host;
				break;

			default:
				$url = $host. '/enter/'. $_url;
				break;
		}

		if($url)
		{
			\dash\redirect::to($url);
		}
	}


	/**
	 * check password syntax
	 * min
	 * max
	 *
	 * @param      <type>  $_password  The password
	 */
	public static function check_pass_syntax($_password, $_debug = true)
	{
		// cehck min leng of password is 6 character
		if(mb_strlen($_password) < 6)
		{
			if($_debug)
			{
				\dash\notif::error(T_("You must set 6 character and large in password"));
			}
			return false;
		}

		// cehck max length of password
		if(mb_strlen($_password) > 99)
		{
			if($_debug)
			{
				\dash\notif::error(T_("You must set less than 99 character in password"));
			}
			return false;
		}

		// no problem ;)
		return true;
	}
}
?>