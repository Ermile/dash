<?php
namespace addons\content_api\v1\home\tools;

trait options
{

	/**
	 * make host string
	 *
	 * @param      string  $_type  The type
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function host($_type = null)
	{

		$host = Protocol."://" . \lib\router::get_root_domain();

		if(defined('simulation_com') && simulation_com)
		{
			$host = simulation_com;
		}

		$lang = \lib\define::get_current_language_string();

		switch ($_type)
		{
			case 'file':
				return $host;
				break;

			case 'without_language':
				return $host;
				break;

			case 'with_language':
				return $host . $lang;
				break;

			default:
				return $host;
				break;
		}
	}


	/**
	 * check permission
	 *
	 * @param      <type>  $_content     The content
	 * @param      <type>  $_permission  The permission
	 * @param      <type>  $_actions     The actions
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function check_api_permission($_caller, $_action = null, $_user_id = null)
	{
		\lib\permission::$user_id = $_user_id;
		return \lib\permission::access(...func_get_args());
	}

}
?>