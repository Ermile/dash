<?php
namespace addons\content_enter\main;

trait _use
{
	/**
	 * use other functions
	 */
	use \addons\content_enter\main\tools\check_input;
	use \addons\content_enter\main\tools\user_data;
	use \addons\content_enter\main\tools\go_to;
	use \addons\content_enter\main\tools\SESSION;
	use \addons\content_enter\main\tools\verification_code;
	use \addons\content_enter\main\tools\send_code;
	use \addons\content_enter\main\tools\lock;
	use \addons\content_enter\main\tools\error;
	use \addons\content_enter\main\tools\request_method;
	use \addons\content_enter\main\tools\login;
	use \addons\content_enter\main\tools\alert;

	/**
	 * in debug mode disable check route and lock moduls
	 * jusn in Tld === dev works
	 * @var        boolean
	 */
	public static $debug_mode        = false;

	/**
	 * dev mode
	 * make code as 11111
	 *
	 * @var        boolean
	 */
	public static $dev_mode          = false;

	/**
	 * for admin user to login by another
	 * to load data by this
	 *
	 * @var        boolean
	 */
	public static $have_main_account = false;

	/**
	 * user mobile
	 * to load data by this
	 *
	 * @var        <type>
	 */
	public static $mobile            = null;

	/**
	 * username
	 *
	 * @var        <type>
	 */
	public static $username          = null;

	/**
	 * user email
	 * to load data by this
	 *
	 * @var        <type>
	 */
	public static $email             = null;
	/**
	 * the user id
	 * to load data by this
	 *
	 * @var        <type>
	 */
	public static $user_id           = null;

	// public static $block_type     = 'ip';
	public static $block_type        = 'session';

	// config to send to javaScript
	public static $wait              = 0;

	// show resende link ofter
	public static $resend_after      = 30; // 30'

	// life time code to expire
	public static $life_time_code    = 60 * 5 * 3; // 5 min * 3 = 15 min

	/**
	 * send code rate
	 * you can custumise in very project in option.php -> self::$enter['send_rate']
	 *
	 * @var        array
	 */
	public static $send_rate =
	[
		'telegram',
		'sms',
		'call',
		'sendsms',
	];


	/**
	 * resend code rating
	 * you can custumise in very project in option.php -> self::$enter['resend_rate']
	 *
	 * @var        array
	 */
	public static $resend_rate =
	[
		'telegram',
		'sms',
		'call',
		'sendsms',
	];

	/**
	 * send sms rate
	 *
	 * @var        array
	 */
	public static $sms_rate =
	[
		'kavenegar',
		// other
		// not now!
	];


	/**
	 * this status of user move to block page
	 *
	 * @var        array
	 */
	public static $block_status =
	[
		'filter',
		'block',
	];


	/**
	* sleep code
	*/
	public static function sleep_code($_seconds = null)
	{
		if($_seconds && is_int($_seconds))
		{
			sleep($_seconds);
		}
		elseif(self::$wait)
		{
			sleep(self::$wait);
		}
		else
		{
			sleep(1);
		}
	}
}
?>