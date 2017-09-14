<?php
namespace lib\telegram\commands;
// use telegram class as bot
use \lib\telegram\tg as bot;

class menu_profile
{
	/**
	 * create profile menu that allow user to select
	 * @param  boolean $_onlyMenu [description]
	 * @return [type]             [description]
	 */
	public static function profile($_onlyMenu = false)
	{
		// define
		$menu =
		[
			'keyboard' =>
			[
				// get other detail as soon as posible
				// ["تکمیل پروفایل"],
				[
					[
						'text'             => T_('Register Address'),
						'request_location' => true
					],
					[
						'text'            => T_('Register Mobile Number'),
						'request_contact' => true
					],
				],
				[T_('Return')],
			],
			// "one_time_keyboard" => true,
			// "force_reply"       => true
		];
		if($_onlyMenu)
		{
			return $menu;
		}

		$txt_text = "*". T_('Profile'). "*\r\n\n";
		$txt_text .= T_('By completing your profile, help us to present better service.')."\n";
		$txt_text .= T_('We will thank you for this.');
		$result   =
		[
			[
				'text'         => $txt_text,
				'reply_markup' => $menu,
			],
		];

		// return menu
		return $result;
	}


	/**
	 * [profile description]
	 * @param  boolean $_onlyMenu [description]
	 * @return [type]             [description]
	 */
	public static function getContact($_onlyMenu = false)
	{
		// define
		$menu =
		[
			'keyboard' =>
			[
				[
					[
						'text'            => T_('Register Mobile Number 📱'),
						'request_contact' => true
					],
				],
				[T_('Return')],
			],
			// "one_time_keyboard" => true,
			// "force_reply"       => true
		];
		if($_onlyMenu)
		{
			return $menu;
		}

		$txt_text = "*". T_('Profile'). "*\r\n\n";
		$txt_text .= T_('By completing your profile, help us to present better service.')."\n";
		$txt_text .= T_('We will thank you for this.');
		$result   =
		[
			[
				'text'         => $txt_text,
				'reply_markup' => $menu,
			],
		];

		// return menu
		return $result;
	}
}
?>