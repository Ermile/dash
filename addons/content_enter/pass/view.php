<?php
namespace addons\content_enter\pass;


class view extends \addons\content_enter\main\view
{
	public function config()
	{
		// read parent config to fill the mobile input and other thing
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

		$this->data->page['title'] = T_('Password');
		$this->data->page['desc']  = T_('Please enter password to enter');

		switch (\lib\router::get_url(1))
		{
			case 'recovery':
				$this->data->page['title'] = T_('Recovery Password');
				$this->data->page['desc']  = T_('If forget your password, Please enter new password. after pass verification your new password is usable.');
				break;

			case 'signup':
			case 'set':
				$this->data->page['title'] = T_('Set Password');
				$this->data->page['desc']  = T_('Please set your password to secure signup.'). ' '. T_('Next time we only need your mobile and this password to enter');
				break;

			case 'change':
				$this->data->page['title'] = T_('Change to new Password');
				$this->data->page['desc']  = T_('Please set your old and new password to change it');
				break;

			default:
				break;
		}
	}
}
?>