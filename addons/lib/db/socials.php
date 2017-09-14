<?php
namespace lib\db;

/** socials managing **/
class socials
{
	/**
	 * this library work with socials
	 * v1.0
	 */


	/**
	 * insert new recrod in socials table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert($_args)
	{
		return \lib\db\config::public_insert('socials', ...func_get_args());
	}


	/**
	 * update
	 *
	 * @param      <type>  $_args  The arguments
	 * @param      <type>  $_id    The identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function update($_args, $_id)
	{
		return \lib\db\config::public_update('socials', ...func_get_args());
	}

	/**
	 * get data
	 *
	 * @param      <type>  $_where  The where
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get($_where)
	{
		return \lib\db\config::public_get('socials', ...func_get_args());
	}


	/**
	 * save google result in social table
	 *
	 * @param      <type>  $_user_id    The user identifier
	 * @param      <type>  $_user_info  The user information
	 */
	public static function google_save($_user_id, $_user_info)
	{
		if(!$_user_id || !is_numeric($_user_id))
		{
			return false;
		}

		$args            = [];
		$args['user_id'] = $_user_id;
		$args['meta']    = json_encode(['result' => $_user_info], JSON_UNESCAPED_UNICODE);

		if(isset($_user_info['email']) && is_string($_user_info['email']))
		{
			$args['email'] = $_user_info['email'];
		}

		if(isset($_user_info['familyName']) && is_string($_user_info['familyName']))
		{
			$args['family'] = $_user_info['familyName'];
		}

		if(isset($_user_info['gender']) && is_string($_user_info['gender']))
		{
			$args['gender'] = $_user_info['gender'];
		}

		if(isset($_user_info['givenName']) && is_string($_user_info['givenName']))
		{
			$args['name'] = $_user_info['givenName'];
		}

		if(isset($_user_info['hd']) && is_string($_user_info['hd']))
		{
			$args['hd'] = $_user_info['hd'];
		}

		if(isset($_user_info['id']) && is_string($_user_info['id']))
		{
			$args['code'] = $_user_info['id'];
		}

		if(isset($_user_info['link']) && is_string($_user_info['link']))
		{
			$args['link'] = $_user_info['link'];
		}

		if(isset($_user_info['locale']) && is_string($_user_info['locale']))
		{
			$args['language'] = $_user_info['locale'];
		}

		if(isset($_user_info['name']) && is_string($_user_info['name']))
		{
			$args['displayname'] = $_user_info['name'];
		}

		if(isset($_user_info['picture']) && is_string($_user_info['picture']))
		{
			$args['picture'] = $_user_info['picture'];
		}

		if(isset($_user_info['verifiedEmail']) && is_string($_user_info['verifiedEmail']))
		{
			$args['verified'] = $_user_info['verifiedEmail'];
		}


		return self::insert($args);
	}
}
?>