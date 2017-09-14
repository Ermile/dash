<?php
namespace addons\content_enter\main\tools;
use \lib\utility;
use \lib\debug;

trait check_input
{
	/**
	 * check posted mobile whit saved mobile in session
	 */
	public static function check_input_current_mobile($_mobile = null)
	{
		if($_mobile === null)
		{
			$_mobile = utility::post('mobile');
		}

		if(intval(utility::post('mobile')) === intval(self::get_enter_session('mobile')))
		{
			return true;
		}

		if(intval(utility::post('mobile')) === intval(self::get_enter_session('temp_mobile')))
		{
			return true;
		}

		return false;
	}


	/**
	 * check the passwrod input is null
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function check_password_is_null()
	{
		if(utility::post('password'))
		{
			return false;
		}
		return true;
	}


	/**
	 * check valid route page
	 *
	 * @param      <type>  $_url   The url
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function check_valid_route($_url)
	{
		$return = false;
		switch ($_url)
		{
			// in step mobile (first step)
			case 'mobile':
				$return = true;
				break;

			default:
				# code...
				break;
		}
		return $return;
	}


	/**
	 * cehck input in every step
	 *
	 * @param      <type>  $_url   The url
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function check_input($_url = null)
	{

		$return = false;
		switch ($_url)
		{
			// in step mobile (first step)
			case 'mobile':
				// just when only posted 1 item and this item is mobile can continue
				if(count(utility::post()) === 2 && utility::post('mobile') && !utility::post('password'))
				{
					$return = true;
				}
				break;

			default:
				$return = true;
				break;
		}
		return $return;
	}
}
?>