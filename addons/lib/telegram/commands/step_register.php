<?php
namespace lib\telegram\commands;
// use telegram class as bot
use \lib\telegram\tg as bot;
use \lib\telegram\step;

class step_register
{
	/**
	 * create define menu that allow user to select
	 * @param  boolean $_onlyMenu [description]
	 * @return [type]             [description]
	 */
	public static function start($_caller, $_lastStep = 'start', $_defaultMenu = null)
	{
		step::start('register');
		// set caller name and step
		step::set('call_from', $_caller);
		step::set('call_from_step', $_lastStep);
		step::set('call_default_menu', $_defaultMenu);

		if(bot::$user_id)
		{
			return self::finish('registered');
		}
		else
		{
			return self::step1();
		}
	}


	/**
	 * show please send contact message
	 * @return [type] [description]
	 */
	public static function step1()
	{
		// after this go to next step
		step::plus();
		// // do not need to save text of contact if called!
		// step::set('saveText', false);
		// show give contact menu
		$menu     = menu_profile::getContact(true);
		$txt_text = T_('Dear user'). "\n";
		$txt_text .= T_('For using this service we need to register your phone number.')."\n";
		$txt_text .= T_('Please send your number with below keyboard to complete registeration.');
		$result   =
		[
			'text'         => $txt_text,
			'reply_markup' => $menu,
		];
		// return menu
		return $result;
	}


	/**
	 * handle user input
	 * @return [type] [description]
	 */
	public static function step2()
	{
		// // do not need to save text of contact if called!
		// step::set('saveText', false);
		// increase limit valu
		step::plus(1, 'limit');
		// if user more than 3 times do not send contact go to main menu
		if(step::get('limit') >3)
		{
			// call stop function
			return self::finish('limit');
		}

		$cmd = bot::$cmd;
		// if user send his/her profile contact detail
		switch ($cmd['command'])
		{
			case 'type_contact':
				// show successful for define question
				$result = self::finish('successful');
				break;

			case T_('return'):
			case 'return':
			case '/return':
				$result = self::finish('return');
				break;

			default:
				$result = self::finish('wrong');
				break;
		}

		return $result;
	}


	/**
	 * finisht register process
	 * @param  [type] $_status [description]
	 * @return [type]          [description]
	 */
	private static function finish($_status)
	{
		$result =
		[
			'text'         => "",
			'reply_markup' => menu::main(true),
		];
		switch ($_status)
		{
			case 'limit':
				$txt = T_('Dear friend')."\n";
				$txt .= T_('We need your phone number to verifying your account.')."\n";
				$txt .= T_('If you dont want share your number, we cant presenting our services to you!')."\n";
				$result['text'] = $txt;
				step::stop();
				break;

			case 'successful':
				$txt = T_('Registering user is successful.')."\n";
				$result['text']         = $txt;
				$result['reply_markup'] = null;
				bot::sendResponse($result);
			case 'registered':
				// check if want to come back to specefic step, do it
				$result = self::successful();
				break;

			case 'failed':
				$result = false;
			case 'return':
				$txt            = T_('Cancel registration and return to main menu')."\n";
				$result['text'] = $txt;
				step::stop();
				break;

			case 'wrong':
			default:
				// else send messge to attention to user to only send contact detail
				$txt  = T_('Please only use below keyboard.');
				$txt  = T_('We need your contact!')."\n";
				$menu = menu_profile::getContact(true);
				$result['text']         = $txt;
				$result['reply_markup'] = $menu;
				break;
		}
		// return menu
		return $result;
	}


	/**
	 * on successful ending call caller step
	 * @return [type] [description]
	 */
	private static function successful()
	{
		// generate caller function to continue last step
		$funcName = '\\'.step::get('call_from'). "::". step::get('call_from_step');
		// stop registration after use variables
		step::stop();
		if(is_callable($funcName))
		{
			// get and return response
			$result = call_user_func($funcName, true, true);
		}
		// else show main menu
		else
		{
			$txt = T_('Return');
			$result =
			[
				'text'         => $txt,
				// 'reply_markup' => menu::main(true),
				'reply_markup' => step::get('call_default_menu'),

			];
			// show main menu
		}
		return $result;
	}

}
?>