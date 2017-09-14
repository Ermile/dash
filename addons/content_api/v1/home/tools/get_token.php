<?php
namespace addons\content_api\v1\home\tools;
use \lib\utility;
use \lib\debug;
use \addons\content_enter\main\tools\token;

trait get_token
{
	/**
	 * make token
	 *
	 * @return     array  ( description_of_the_return_value )
	 */
	public function token($_guest = false)
	{

		$guest_token = null;
		if(utility::request("guest"))
		{
			$guest_token = utility::request("guest");
		}

		$token = null;
		if($_guest)
		{
			$token = token::create_guest($this->authorization);
		}
		else
		{
			$token = token::create_tmp_login($this->authorization, $guest_token);
		}
		return ['token' => $token];
	}


	/**
	 * check verified temp token or no
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function check_verify()
	{
		$temp_token = utility::request("temp_token");
		if(!$temp_token)
		{
			if(debug::$status)
			{
				debug::error(T_("Invalid parameter temp_token"), 'temp_token', 'arguments');
			}
			return false;
		}
		return token::check_verify($temp_token);
	}

}
?>