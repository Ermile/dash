<?php
namespace addons\content_enter\google;
use \lib\debug;
use \lib\utility;
use \lib\social\google;

// class google
// {
// 	use \addons\content_enter\main\_use;

// 	public static function check()
// 	{
// 		return true;
// 	}

// 	public static function user_info($_key = null)
// 	{
// 		return self::user_data($_key);
// 	}
// }

class model extends \addons\content_enter\main\model
{
	public function get_google()
	{
		if(utility::get('code'))
		{
			// check access from google
			$check = google::check();
			if($check)
			{
				// go to what url
				$go_to_url           = null;

				$no_problem_to_login = false;

				$user_data = google::user_info();
				// get user email
				self::$email = google::user_info('email');
				// load data by email in field user google mail
				self::load_user_data('email', ['email_field' => 'googlemail']);
				// the user exist in system
				if(self::user_data('id'))
				{
					// check user status
					if(in_array(self::user_data('status'), self::$block_status))
					{
						// the user was blocked
						self::next_step('block');
						// go to block page
						self::go_to('block');

						return false;
					}
					else
					{
						if(self::user_data('mobile'))
						{
							// no problem to login this user
							$no_problem_to_login = true;
						}
						else
						{
							if(self::user_data('dontwillsetmobile'))
							{
								if(strtotime(self::user_data('dontwillsetmobile')) > (60*60*24*365))
								{
									self::set_enter_session('mobile_request_from', 'google_email_exist');

									self::set_enter_session('logined_by_email', google::user_info('email'));
									// go to mobile get to enter mobile
									self::next_step('mobile/request');
									// get go to url
									$go_to_url = 'mobile/request';
								}
								else
								{
									// no problem to login this user
									$no_problem_to_login = true;
								}
							}
							else
							{
								self::set_enter_session('mobile_request_from', 'google_email_exist');

								self::set_enter_session('logined_by_email', google::user_info('email'));
								// go to mobile get to enter mobile
								self::next_step('mobile/request');
								// get go to url
								$go_to_url = 'mobile/request';
							}
						}
					}
				}
				else
				{
					// the email of this user is not exist in system
					$args = [];
					if(google::user_info('name'))
					{
						$args['displayname'] = google::user_info('name');
					}
					elseif(google::user_info('familyName') || google::user_info('givenName'))
					{
						$args['displayname'] = trim(google::user_info('familyName'). ' '. google::user_info('givenName'));
					}

					$args['googlemail'] = google::user_info('email');
					$args['datecreated']  = date("Y-m-d H:i:s");

					self::set_enter_session('mobile_request_from', 'google_email_not_exist');

					self::set_enter_session('must_signup', $args);

					self::set_enter_session('logined_by_email', google::user_info('email'));

					// $user_id = self::signup($args);
					// // save user data in socials table
					// \lib\db\socials::google_save($user_id, google::user_info());

					// go to mobile get to enter mobile
					self::next_step('mobile/request');
					// get go to url
					$go_to_url = 'mobile/request';
				}

				if($no_problem_to_login)
				{
					// set login session
					$redirect_url = self::enter_set_login();
					self::set_enter_session('redirect_url', $redirect_url);
					// save redirect url in session to get from okay page
					// set okay as next step
					self::next_step('okay');
					// go to okay page
					self::go_to('okay');
				}
				else
				{
					// go to url
					if($go_to_url)
					{
						self::go_to($go_to_url);
					}
					else
					{
						self::set_alert(T_("System error! try again"));
						self::go_to('alert');
					}
				}
			}
			else
			{
				return false;
			}
		}
	}
}
?>