<?php
namespace addons\content_enter\main\tools;
use \lib\utility;
use \lib\debug;

trait go_to
{
	/**
	 * redirect to url
	 *
	 * @param      <type>  $_url   The url
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function go_to($_url = null)
	{

		switch ($_url)
		{
			// redirect to base
			case 'base':
				$host = Protocol."://" . \lib\router::get_root_domain();
				$host .= \lib\define::get_current_language_string();
				$host .= '/enter';
				self::go_redirect($host);
				break;

			case 'main':
				$host = Protocol."://" . \lib\router::get_root_domain();
				$host .= \lib\define::get_current_language_string();
				self::go_redirect($host);
				break;

			case 'okay':
				if($url = self::get_enter_session('redirect_url'))
				{
					self::go_redirect($url, false, true);
				}
				break;

			default:
				self::go_redirect($_url);
				break;
		}
	}


	/**
	 * set redirect url
	 *
	 * @param      <type>  $_url   The url
	 */
	public static function go_redirect($_url, $_return = false, $_direct = false)
	{
		if(isset($_SESSION['go_to']) && is_array($_SESSION['go_to']))
		{
			array_push($_SESSION['go_to'], $_url);
		}
		else
		{
			$_SESSION['go_to'] = [$_url];
		}

		if($_direct)
		{
			debug::msg('direct', true);
		}

		$redirect = new \lib\redirector($_url);

		if($_return)
		{
			return $redirect->redirect(true);
		}
		else
		{
			$redirect->redirect();
		}

	}
}
?>