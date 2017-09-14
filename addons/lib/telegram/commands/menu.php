<?php
namespace lib\telegram\commands;
// use telegram class as bot
use \lib\telegram\tg as bot;

class menu
{
	public static $return = false;

	public static function exec($_cmd)
	{
		$response = null;
		switch ($_cmd['command'])
		{
			case '/menu':
			case '/cancel':
			case '/stop':
			case 'menu':
			case 'main':
			case 'mainmenu':
			case 'منو':
				$response = self::main();
				break;

			case '/intro':
			case 'intro':
			case '/tour':
			case 'tour':
			case 'معرفی':
				$response = self::intro();
				break;


			case '/feature':
			case 'feature':
			case 'امکانات':
			case 'امکانات هتل':
				$response = self::feature();
				break;

			case '/general':
			case 'general':
			case 'مشخصات':
			case 'مشخصات عمومی':
				$response = self::general();
				break;

			case '/list':
			case 'list':
			case 'لیست':
			case 'لیست اتاق‌ها':
				$response = self::room_list();
				break;

			case '/standard':
			case 'standard':
			case 'استاندارد':
				$response = self::room_standard();
				break;

			case '/modern':
			case 'modern':
			case 'مدرن':
				$response = self::room_modern();
				break;

			case '/family':
			case 'family':
			case 'خانواده':
				$response = self::room_family();
				break;

			case '/lux':
			case 'lux':
			case 'مجلل':
				$response = self::room_lux();
				break;


			case 'loc':
			case 'موقعیت':
				$response = self::menu_loc();
				break;


			case 'inline':
			case 'اینلاین':
				$response = self::menu_inline();
				break;

			case 'return':
			case 'بازگشت':
				switch ($_cmd['text'])
				{
					case 'بازگشت به منوی اصلی':
						$response = self::main();
						break;

					case 'بازگشت به منوی معرفی':
						$response = self::intro();
						break;

					default:
						$response = self::main();
						break;
				}
				break;

			default:
				break;
		}

		// automatically add return to end of keyboard
		if(self::$return)
		{
			// if has keyboard
			if(isset($response['replyMarkup']['keyboard']))
			{
				$response['replyMarkup']['keyboard'][] = ['بازگشت'];
			}
		}

		return $response;
	}


	/**
	 * create mainmenu
	 * @param  boolean $_onlyMenu [description]
	 * @return [type]             [description]
	 */
	public static function main($_onlyMenu = false)
	{
		// define
		$menu =
		[
			'keyboard' =>
			[
				["معرفی _type_"],
				["درباره _type_", "تماس با ما"],
			],
		];

		if($_onlyMenu)
		{
			return $menu;
		}

		$result =
		[
			[
				'text'         => "*_fullName_*\r\n\nمنوی اصلی",
				'reply_markup' => $menu,
			],
		];

		// return menu
		return $result;
	}


	/**
	 * create mainmenu
	 * @param  boolean $_onlyMenu [description]
	 * @return [type]             [description]
	 */
	public static function intro($_onlyMenu = false)
	{
		// define
		$menu =
		[
			'keyboard' =>
			[
				["لیست اتاق‌ها"],
				["مشخصات عمومی", "امکانات هتل"],
				["بازگشت به منوی اصلی"],
			],
		];

		if($_onlyMenu)
		{
			return $menu;
		}

		$result =
		[
			[
				'text'         => "*_fullName_*\r\n\n_intro_",
				'reply_markup' => $menu,
			],
		];

		// return menu
		return $result;
	}


	/**
	 * show features message
	 * @return [type] [description]
	 */
	public static function feature()
	{
		$result['caption'] = "_feature_";
		// $result['photo']   = new \CURLFile(realpath("static/images/telegram/features.jpg"));
		$result['photo']   = 'AgADBAADt6cxG-eq1QmndQ_2kwo3PXstQxkABG-c3dnLJoA0QncAAgI';
		$result['method']  = "sendPhoto";

		return $result;
	}


	/**
	 * show general message
	 * @return [type] [description]
	 */
	public static function general()
	{

		$result['caption'] = "_general_";
		// $result['photo']   = new \CURLFile(realpath("static/images/telegram/general.jpg"));
		$result['photo']   = 'AgADBAADuqcxG-eq1Ql_FJNHU8eJL6xEKRkABM3ZsAhFwe5jk3YBAAEC';
		$result['method']  = "sendPhoto";

		return $result;
	}


	public static function room_standard()
	{

		$result['caption'] = "سوییت استاندارد";
		// $result['photo']   = new \CURLFile(realpath("static/images/telegram/room-standard.jpg"));
		$result['photo']   = 'AgADBAADu6cxG-eq1Qk6Y0Vf_YPNoMKBJBkABGYn-91Fgx5g3HcBAAEC';
		$result['method']  = "sendPhoto";

		return $result;
	}

	public static function room_modern()
	{

		$result['caption'] = "سوییت مدرن";
		// $result['photo']   = new \CURLFile(realpath("static/images/telegram/room-modern.jpg"));
		$result['photo']   = 'AgADBAADvKcxG-eq1QmH92pAF08T_xfCQRkABOErQh4z46YSY3YAAgI';
		$result['method']  = "sendPhoto";

		return $result;
	}

	public static function room_family()
	{

		$result['caption'] = "سوییت خانواده";
		// $result['photo']   = new \CURLFile(realpath("static/images/telegram/room-family.jpg"));
		$result['photo']   = 'AgADBAADvacxG-eq1Ql4XJOmaYcUE8xJQxkABBGnNrqILNvyInYAAgI';
		$result['method']  = "sendPhoto";

		return $result;
	}

	public static function room_lux()
	{

		$result['caption'] = "سوییت مجلل";
		// $result['photo']   = new \CURLFile(realpath("static/images/telegram/room-lux.jpg"));
		$result['photo']   = 'AgADBAADvqcxG-eq1Qm3eUf_PGzhYCDmKBkABMfq8W8TqeP1MnoBAAEC';
		$result['method']  = "sendPhoto";

		return $result;
	}


	/**
	 * create mainmenu
	 * @param  boolean $_onlyMenu [description]
	 * @return [type]             [description]
	 */
	public static function room_list($_onlyMenu = false)
	{
		// define
		$menu =
		[
			'keyboard' =>
			[
				["مدرن", "استاندارد"],
				["مجلل", "خانواده"],
				["بازگشت به منوی معرفی"],
			],
		];

		if($_onlyMenu)
		{
			return $menu;
		}

		$result =
		[
			[
				'text'         => "_list_",
				'reply_markup' => $menu,
			],
		];

		// return menu
		return $result;
	}






	/**
	 * return menu
	 * @return [type] [description]
	 */
	public static function menu_loc()
	{
		$result['text']        = 'منوی موقعیت'."\r\n";
		$result['replyMarkup'] =
		[
			'keyboard' =>
			[
				[
					[
						'text'            => 'تقاضای شماره تلفن',
						'request_contact' => true
					],
					[
						'text'             => 'تقاضای آدرس',
						'request_location' => true
					]
				]
			]
		];
		return $result;
	}


	/**
	 * return menu
	 * @return [type] [description]
	 */
	public static function menu_inline()
	{
		$result['text']        = 'منوی اینلاین آزمایشی'."\r\n";
		$result['replyMarkup'] =
		[
			'inline_keyboard' =>
			[
				[
					[
						'text'          => '<',
						'callback_data' => 'go_left'
					],
					[
						'text'          => '^',
						'callback_data' => 'go_up'
					],
					[
						'text'          => '>',
						'callback_data' => 'go_right'
					],
				],
				[
					[
						'text' => 'open google.com',
						'url'  => 'google.com'
					],
					[
						'text'                => 'search \'test\' inline',
						'switch_inline_query' => 'test'
					],
				]
			],
		];
		return $result;
	}
}
?>