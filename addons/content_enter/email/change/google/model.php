<?php
namespace addons\content_enter\email\change\google;
use \lib\utility;
use \lib\debug;
use \lib\db;

class model extends \addons\content_enter\main\model
{

	/**
	 * Posts an enter.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function post_change_google($_args)
	{
		if(utility::post('update') === 'no')
		{
			self::set_alert(T_("Please log in with your previous email or ignore your mobile registration."));
			self::next_step('alert');
			self::go_to('alert');
			return;
		}

		$old_google_mail = self::get_enter_session('old_google_mail');
		$new_google_mail = self::get_enter_session('new_google_mail');

		$user_id = self::get_enter_session('user_id_must_change_google_mail');
		if($old_google_mail && $new_google_mail && is_numeric($user_id))
		{
			self::$user_id = $user_id;
			\lib\db\users::update(['googlemail' => $new_google_mail], $user_id);
			self::load_user_data('user_id');
			self::enter_set_login();
			self::next_step('okay');
			self::go_to('okay');
		}

	}
}
?>