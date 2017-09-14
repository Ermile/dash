<?php
namespace lib\db;
use \lib\debug;
use \lib\utility;
use \lib\db;

/** transactions managing **/
class transactions
{

	use transactions\set;
	use transactions\budget;


	/**
	 * insert new record of transactions
	 *
	 * @param      <type>  $_arg   The argument
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private static function insert()
	{
		db\config::public_insert('transactions', ...func_get_args());
		return db::insert_id();
	}


	/**
	 * update transactions
	 *
	 * @param      <type>  $_args  The arguments
	 * @param      <type>  $_id    The identifier
	 */
	public static function update()
	{
		return db\config::public_update('transactions', ...func_get_args());
	}


	/**
	 * get the transaction record
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get()
	{
		$result = db\config::public_get('transactions', ...func_get_args());
		$result = self::ready($result);
		return $result;
	}


	/**
	 * Searches for the first match.
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function search($_string = null, $_options = [])
	{
		if(!is_array($_options))
		{
			$_options = [];
		}

		$default_option =
		[
			'search_field' =>
			"
				(
					users.mobile LIKE '%__string__%' OR
					users.email LIKE '%__string__%' OR
					transactions.title LIKE '%__string__%'
				)

			",
			'public_show_field' =>
				"
					transactions.*,
					users.mobile      AS `mobile`,
					users.displayname AS `displayname`
				",
			'master_join'         => " INNER JOIN users ON users.id = transactions.user_id ",
		];

		$_options = array_merge($default_option, $_options);

		$result = db\config::public_search('transactions', $_string, $_options);
		$result = self::ready($result, true);
		return $result;
	}

	/**
	 * change some field to show
	 *
	 * @param      <type>  $_result  The result
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function ready($_result, $_multi_record = false)
	{
		if($_result && is_array($_result))
		{
			if($_multi_record)
			{
				foreach ($_result as $key => $value)
				{
					if(isset($value['unit_id']))
					{
						$_result[$key]['unit'] = \lib\utility\units::get($value['unit_id'], true);
					}

					if(isset($value['code']))
					{
						$_result[$key]['code'] = self::get_caller($value['code']);
					}
				}

			}
			else
			{
				if(isset($_result['unit_id']))
				{
					$_result['unit'] = \lib\utility\units::get($_result['unit_id'], true);
				}

				if(isset($_result['code']))
				{
					$_result['code'] = self::get_caller($_result['code']);
				}
			}
		}
		return $_result;
	}
}
?>