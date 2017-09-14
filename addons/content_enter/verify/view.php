<?php
namespace addons\content_enter\verify;


class view extends \addons\content_enter\main\view
{
	/**
	 * config
	 */
	public function config()
	{
		parent::config();

		// change inpu
		if(self::done_step('username'))
		{
			// if user go to this page from username page
			$this->data->mobile_username = 'eusername';
		}
		else
		{
			// if user go to this page from mobile page
			$this->data->mobile_username = 'emobile';
		}
		// load temp username in username field
		if(self::get_session('username', 'temp_username'))
		{
			$this->data->get_username = self::get_session('username', 'temp_username');
		}

		// the verify msg
		$myDesc  = T_('Please verify yourself.'). ' ';

		switch (self::get_enter_session('verification_code_way'))
		{
			case 'telegram':
				$myDesc .= T_("We've sent the code via Telegram. Please enter the code below.");
				break;

			case 'call':
				$myDesc .= T_("We trying to call you. be patient...");
				break;

			case 'sms':
				$myDesc .= T_("Your verification code sended to your mobile number via sms. be patient...");
				break;

			case 'sendsms':
				$myDesc .= T_("We can't send code to you with our existing methods! For the last chance of verify yourself you can send code to our number.");
				break;

			default:
				$myDesc .= T_("What happend?");
				break;
		}



		// swich verify from
		switch (self::get_enter_session('verify_from'))
		{
			// user from signup go to this page
			case 'signup':
			case 'set':
				// $myDesc .= T_("Your verification code send to your telegram.");
				break;

			// user from delete go to this page
			case 'delete':
				$myDesc .= ' '. T_("This is request of delete account!");
				break;

			// user from recovery go to this page
			case 'recovery':
				$myDesc .= ' '. T_("This is request of account recovery.");
				break;

			// user from change password go to this page
			case 'change':
				// swich way
				$myDesc .= ' '. T_("This is request of change password.");
				break;
		}

		$myDesc                 = trim($myDesc);
		$this->data->verify_msg = $myDesc;
		$myTitle                = T_('Verify');
		// set title of pages
		switch (\lib\router::get_url(1))
		{
			case 'call':
				$myTitle = T_('Verify by Call');
				break;

			case 'telegram':
				$myTitle = T_('Verify via Telegram');
				break;

			case 'sms':
				$myTitle = T_('verify with SMS');
				break;
		}

		$this->data->page['title'] = $myTitle;
		$this->data->page['desc']  = $myDesc;
	}
}
?>