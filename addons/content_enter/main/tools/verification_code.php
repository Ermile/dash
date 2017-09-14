<?php
namespace addons\content_enter\main\tools;
use \lib\utility;
use \lib\debug;

trait verification_code
{

	/**
	 * generate verification code
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function create_new_code($_way = null)
	{
		$code =  rand(10000,99999);
		if(self::$dev_mode)
		{
			$code = 11111;
		}
		// set verification code in session
		self::set_enter_session('verification_code', $code);
		$time = date("Y-m-d H:i:s");

		$log_meta =
		[
			'data'     => $code,
			'desc' => $_way,
			'time'     => $time,
			'meta'     =>
			[
				'session' => $_SESSION,
			],
		];


		// save this code in logs table and session
		$log_id = \lib\db\logs::set('user:verification:code', self::user_data('id'), $log_meta);

		self::set_enter_session('verification_code', $code);
		self::set_enter_session('verification_code_time', $time);
		self::set_enter_session('verification_code_way', $_way);
		self::set_enter_session('verification_code_id', $log_id);

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
			self::get_enter_session('verification_code') &&
			self::get_enter_session('verification_code_id') &&
			self::get_enter_session('verification_code_time')
		)
		{
			if(time() - strtotime(self::get_enter_session('verification_code_time')) < self::$life_time_code)
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
				$where =
				[
					'caller'     => 'user:verification:code',
					'user_id'    => self::user_data('id'),
					'status' => 'enable',
					'limit'      => 1,
				];
				$log_code = \lib\db\logs::get($where);

				if($log_code)
				{
					if(isset($log_code['datecreated']) && time() - strtotime($log_code['datecreated']) < self::$life_time_code)
					{
						// the last code is okay
						// need less to create new code
						$last_code_ok = true;
						// save data in session
						if(isset($log_code['data']))
						{
							self::set_enter_session('verification_code', $log_code['data']);
						}
						// save log time
						if(isset($log_code['datecreated']))
						{
							self::set_enter_session('verification_code_time', $log_code['datecreated']);
						}
						// save log way
						if(isset($log_code['desc']))
						{
							self::set_enter_session('verification_code_way', $log_code['desc']);
							if($prev_way = self::get_last_way())
							{
								self::set_enter_session('verification_code_way', $prev_way);
							}
						}
						// save log id
						if(isset($log_code['id']))
						{
							self::set_enter_session('verification_code_id', $log_code['id']);
						}

					}
					else
					{
						// the log is exist and the time of log is die
						// we expire the log
						if(isset($log_code['id']))
						{
							\lib\db\logs::update(['status' => 'expire'], $log_code['id']);
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
				'post'    => utility::post(),
			]
		];

		if(!self::check_input_current_mobile())
		{
			debug::error(T_("Dont!"));
			return false;
		}

		if(!utility::post('code'))
		{
			debug::error(T_("Please fill the verification code"), 'code');
			return false;
		}

		if(!is_numeric(utility::post('code')))
		{
			debug::error(T_("What happend? the code is number. you try to send string!?"), 'code');
			return false;
		}

		$code_is_okay = false;
		// if the module is sendsms user not send the verification code here
		// the user send the verification code to my sms service
		// and this code is deffirent by verification code
		if($_module === 'sendsms')
		{
			$code = utility::post('code');
			if($code == self::get_enter_session('sendsms_code'))
			{
				$log_id = self::get_enter_session('sendsms_code_log_id');

				if($log_id)
				{
					$get_log_detail = \lib\db\logs::get(['id' => $log_id, 'limit' => 1]);
					if(!$get_log_detail || !isset($get_log_detail['status']))
					{
						\lib\db\logs::set('enter:verify:sendsmsm:log:not:found', self::user_data('id'), $log_meta);
						debug::error(T_("System error, try again"));
						return false;
					}

					switch ($get_log_detail['status'])
					{
						case 'deliver':
							// the user must be login
							\lib\db\logs::update(['status' => 'expire'], $log_id);
							$code_is_okay = true;
							// set login session
							// $redirect_url = self::enter_set_login();

							// // save redirect url in session to get from okay page
							// self::set_enter_session('redirect_url', $redirect_url);
							// // set okay as next step
							// self::next_step('okay');
							// // go to okay page
							// self::go_to('okay');
							break;

						case 'enable':
							// user not send sms or not deliver to us
							\lib\db\logs::set('enter:verify:sendsmsm:sms:not:deliver:to:us', self::user_data('id'), $log_meta);
							debug::error(T_("Your sms not deliver to us!"));
							return false;
							break;

						case 'expire':
							// the user user from this way and can not use this way again
							// this is a bug!
							\lib\db\logs::set('enter:verify:sendsmsm:sms:expire:log:bug', self::user_data('id'), $log_meta);
							debug::error(T_("What are you doing?"));
							return false;
						default:
							// bug!
							return false;
							break;
					}
				}
				else
				{
					\lib\db\logs::set('enter:verify:sendsmsm:log:id:not:found', self::user_data('id'), $log_meta);
					debug::error(T_("What are you doing?"));
					return false;
				}
			}
			else
			{
				\lib\db\logs::set('enter:verify:sendsmsm:user:inspected:change:html', self::user_data('id'), $log_meta);
				debug::error(T_("What are you doing?"));
				return false;
			}
		}
		else
		{
			if(intval(utility::post('code')) === intval(self::get_enter_session('verification_code')))
			{
				$code_is_okay = true;
			}
		}

		if($code_is_okay)
		{
			// expire code
			if(self::get_enter_session('verification_code_id'))
			{
				// the user enter the code and the code is ok
				// must expire this code
				\lib\db\logs::update(['status' => 'expire'], self::get_enter_session('verification_code_id'));
				self::set_enter_session('verification_code', null);
				self::set_enter_session('verification_code_time', null);
				self::set_enter_session('verification_code_way', null);
				self::set_enter_session('verification_code_id', null);
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
					self::get_enter_session('verify_from') === 'signup' ||
					self::get_enter_session('verify_from') === 'set' ||
					self::get_enter_session('verify_from') === 'recovery'
				) &&
				self::get_enter_session('temp_ramz_hash') &&
				is_numeric(self::user_data('id'))
			  )
			{
				// set temp ramz in use pass
				\lib\db\users::update(['password' => self::get_enter_session('temp_ramz_hash')], self::user_data('id'));
			}


			/**
			 ***********************************************************
			 * VERIFY FROM
			 * USERNAME
			 * TRY TO REMOVE USER NAME
			 ***********************************************************
			 */
			if(self::get_enter_session('verify_from') === 'username_remove' && is_numeric(self::user_data('id')))
			{
				// set temp ramz in use pass
				\lib\db\users::update(['username' => null], self::user_data('id'));
				// remove usename from sessions
				unset($_SESSION['user']['username']);
				// set the alert message
				self::set_alert(T_("Your username was removed"));
				// open lock of alert page
				self::next_step('alert');
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
			if(self::get_enter_session('verify_from') === 'delete')
			{
				if(self::get_enter_session('why'))
				{
					$update_meta  = [];

					$meta = self::user_data('meta');
					if(!$meta)
					{
						$update_meta['why'] = self::get_enter_session('why');
					}
					elseif(is_string($meta) && substr($meta, 0, 1) !== '{')
					{
						$update_meta['other'] = $meta;
						$update_meta['why'] = self::get_enter_session('why');
					}
					elseif(is_string($meta) && substr($meta, 0, 1) === '{')
					{
						$json = json_decode($meta, true);
						$update_meta = array_merge($json, ['why' => self::get_enter_session('why')]);
					}

				}

				$update_user = [];
				if(!empty($update_meta))
				{
					$update_user['meta'] = json_encode($update_meta, JSON_UNESCAPED_UNICODE);
				}
				$update_user['status'] = 'removed';

				\lib\db\users::update($update_user, self::user_data('id'));

				\lib\db\sessions::delete_account(self::user_data('id'));

				//put logout
				self::set_logout(self::user_data('id'), false);
				self::next_step('byebye');
				self::go_to('byebye');
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
					self::get_enter_session('verify_from') === 'username_set' ||
					self::get_enter_session('verify_from') === 'username_change'
				) &&
				self::get_enter_session('temp_username') &&
				is_numeric(self::user_data('id'))
			  )
			{
				// set temp ramz in use pass
				\lib\db\users::update(['username' => self::get_enter_session('temp_username')], self::user_data('id'));
				// set the alert message
				if(self::get_enter_session('verify_from') === 'username_set')
				{
					self::set_alert(T_("Your username was set"));
				}
				else
				{
					self::set_alert(T_("Your username was change"));
				}

				if(isset($_SESSION['user']) && is_array($_SESSION['user']))
				{
					$_SESSION['user']['username'] = self::get_enter_session('temp_username');
				}

				// open lock of alert page
				self::next_step('alert');
				// go to alert page
				self::go_to('alert');
				return;
			}

			/**
			 ***********************************************************
			 * VERIFY FROM
			 * MOBILI/REQUEST
			 ***********************************************************
			 */
			if(self::get_enter_session('verify_from') === 'mobile_request')
			{
				// must loaded mobile data
				if(self::get_enter_session('temp_mobile') && is_numeric(self::get_enter_session('temp_mobile')))
				{
					$load_mobile_data = \lib\db\users::get_by_mobile(self::get_enter_session('temp_mobile'));
					if($load_mobile_data && isset($load_mobile_data['id']))
					{
						if(isset($load_mobile_data['status']) && in_array($load_mobile_data['status'], self::$block_status))
						{
							self::next_step('block');
							self::go_to('block');
							return ;
						}
						else
						{
							if(self::get_enter_session('mobile_request_from') === 'google_email_not_exist')
							{
								if(isset($load_mobile_data['googlemail']) && $load_mobile_data['googlemail'])
								{
									if(self::get_enter_session('logined_by_email') === $load_mobile_data['googlemail'])
									{
										self::$user_id = $load_mobile_data['id'];
										self::load_user_data('user_id');
									}
									else
									{
										self::set_enter_session('old_google_mail', $load_mobile_data['googlemail']);
										self::set_enter_session('new_google_mail', self::get_enter_session('logined_by_email'));
										self::set_enter_session('user_id_must_change_google_mail', $load_mobile_data['id']);
										// request from user to change email
										self::next_step('email/change/google');
										self::go_to('email/change/google');
										return ;
									}
								}
								else
								{
									\lib\db\users::update(['googlemail' => self::get_enter_session('logined_by_email')], $load_mobile_data['id']);
									self::$user_id = $load_mobile_data['id'];
									self::load_user_data('user_id');
								}
							}
							else //if(self::get_enter_session('mobile_request_from') === 'google_email_exist') or more
							{
								self::set_enter_session('request_delete_msg', T_("Duplicate account"));

								self::next_step('delete/request');
								self::go_to('delete/request');
								return ;
							}
						}
					}
					else
					{
						if(self::get_enter_session('mobile_request_from') === 'google_email_not_exist')
						{
							if(self::get_enter_session('must_signup') && is_array(self::get_enter_session('must_signup')))
							{
								$signup = self::get_enter_session('must_signup');
								if(self::get_enter_session('temp_mobile'))
								{
									$signup['mobile'] = self::get_enter_session('temp_mobile');
								}

								if(self::get_enter_session('logined_by_email'))
								{
									$signup['googlemail'] = self::get_enter_session('logined_by_email');
								}

								$signup['status'] = 'active';
								self::set_enter_session('first_signup', true);
								self::$user_id = \lib\db\users::signup($signup);
								self::load_user_data('user_id');
							}
							else
							{
								\lib\db\logs::set('error110000');
							}
						}
						elseif(self::get_enter_session('mobile_request_from') === 'google_email_exist')
						{
							if(!self::user_data('mobile'))
							{
								\lib\db\users::update(['mobile' => self::get_enter_session('temp_mobile')], self::user_data('id'));
								// login
							}
							self::$user_id = self::user_data('id');
							self::load_user_data('user_id');

						}
						else
						{
							// other way go to here
							// facebook not exist email and ...
							\lib\db\logs::set('error110');
							return false;
						}
					}
				}
				else
				{
					// no mobile was found :|
					// bug. return false;
					\lib\db\logs::set('error11');
					return false;
				}
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
					self::get_enter_session('verify_from') === 'email_set' ||
					self::get_enter_session('verify_from') === 'email_change'
				) &&
				self::get_enter_session('temp_email') &&
				is_numeric(self::user_data('id'))
			  )
			{
				// set temp ramz in use pass
				\lib\db\users::update(['email' => self::get_enter_session('temp_email')], self::user_data('id'));
			}

			/**
			 ***********************************************************
			 * VERIFY FROM
			 * TWO STEP VERICICATION
			 ***********************************************************
			 */
			if(self::get_enter_session('verify_from') === 'two_step' &&	is_numeric(self::user_data('id')))
			{
				// no thing yet
			}


			/**
			 ***********************************************************
			 * VERIFY FROM
			 * TWO STEP VERICICATION SET
			 ***********************************************************
			 */
			if(self::get_enter_session('verify_from') === 'two_step_set' &&	is_numeric(self::user_data('id')))
			{
				// set on two_step of this user
				\lib\db\users::update(['twostep' => 1], self::user_data('id'));
			}


			/**
			 ***********************************************************
			 * VERIFY FROM
			 * TWO STEP VERICICATION SET
			 ***********************************************************
			 */
			if(self::get_enter_session('verify_from') === 'two_step_unset' &&	is_numeric(self::user_data('id')))
			{
				// set off two_step of this user
				\lib\db\users::update(['twostep' => 0], self::user_data('id'));
			}

			// set login session
			$redirect_url = self::enter_set_login();

			// save redirect url in session to get from okay page
			self::set_enter_session('redirect_url', $redirect_url);
			// set okay as next step
			self::next_step('okay');
			// go to okay page
			self::go_to('okay');

		}
		else
		{
			// wrong code sleep code
			self::sleep_code();

			// plus count invalid code
			self::plus_try_session('invalid_code');

			debug::error(T_("Invalid code, try again"), 'code');
			return false;
		}
	}


	/**
	 * Sends a code email.
	 * send verification code whit email address
	 */
	public static function send_code_email()
	{
		$email = self::get_enter_session('temp_email');
		$code  = self::generate_verification_code();
		$mail =
		[
			'from'    => 'info@tejarak.com',
			'to'      => $email,
			'subject' => 'contact',
			'body'    => "salam". $code,
			'debug'   => true,
		];
		$mail = \lib\utility\mail::send($mail);
		return $mail;
	}



	/**
	 * user fill the mobile/request
	 * this function find next step
	 * signup user
	 * or login only
	 */
	public static function mobile_request_next_step()
	{
		// set temp ramz in use pass
		switch (self::get_enter_session('mobile_request_from'))
		{
			case 'google_email_not_exist':
				if(self::get_enter_session('must_signup'))
				{
					// sign up user
					self::set_enter_session('first_signup', true);
					$user_id = \lib\db\users::signup(self::get_enter_session('must_signup'));
					if($user_id)
					{
						self::$user_id = $user_id;
						self::load_user_data('user_id');
						// auto redirect to redirect url
						self::enter_set_login(null, true);
						return;
					}
					else
					{
						// can not signup
						return false;
					}
				}
				break;

			case 'google_email_exist':
				if(is_numeric(self::user_data('id')))
				{
					// the user click on dont will mobile
					// we save this time to dontwillsetmobile to never show this message again
					$update_user_google = [];

					if(self::get_enter_session('dont_will_set_mobile'))
					{
						$update_user_google['dontwillsetmobile'] = date("Y-m-d H:i:s");
					}
					if(!empty($update_user_google))
					{
						\lib\db\users::update($update_user_google, self::user_data('id'));
					}
					//auto redirect to redirect url
					self::enter_set_login(null, true);
					return ;
				}

				return false;
				break;

			default:
				# code...
				break;
		}
		return true;
	}
}
?>