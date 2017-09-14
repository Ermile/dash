<?php
namespace lib\db;

/** userparents managing **/
class userparents
{
	/**
	 * insert new notification
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function insert()
	{
		return \lib\db\config::public_insert('userparents', ...func_get_args());
	}


	/**
	 * make multi insert
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function multi_insert()
	{
		return \lib\db\config::public_multi_insert('userparents', ...func_get_args());
	}


	/**
	 * update the notification
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function update()
	{
		return \lib\db\config::public_update('userparents', ...func_get_args());
	}


	/**
	 * get the notification
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get()
	{
		return \lib\db\config::public_get('userparents', ...func_get_args());
	}


	/**
	 * Searches for the first match.
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function search()
	{
		$result = \lib\db\config::public_search('userparents', ...func_get_args());
		return $result;
	}


	/**
	 * Loads a parent.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public static function load_parent($_args)
	{
		$result = self::get($_args);

		if(empty($result))
		{
			return false;
		}

		$parent_ids     = array_column($result, 'parent');
		$parent_ids     = implode(',', $parent_ids);
		$query          = "SELECT * FROM users WHERE users.id IN ($parent_ids) ";
		$parent_details = \lib\db::get($query);
		$user_ids       = array_column($parent_details, 'id');
		$parent_details = array_combine($user_ids, $parent_details);
		$return         = [];


		foreach ($result as $key => $value)
		{
			if(!isset($value['parent']))
			{
				continue;
			}

			if(isset($parent_details[$value['parent']]))
			{
				$temp = [];
				$temp['id']          = $value['id'];
				$temp['fileurl']    = isset($parent_details[$value['parent']]['fileurl'])? $parent_details[$value['parent']]['fileurl'] : null;
				$temp['mobile']      = isset($parent_details[$value['parent']]['mobile'])? $parent_details[$value['parent']]['mobile'] : null;
				$temp['displayname'] = isset($parent_details[$value['parent']]['displayname'])? $parent_details[$value['parent']]['displayname'] : null;
				$temp['title']       = isset($value['title']) ? $value['title'] : null;
				$temp['othertitle']  = isset($value['othertitle'])? $value['othertitle'] : null;

				if($temp['title'] === 'custom' && $temp['othertitle'])
				{
					$temp['title'] = $temp['othertitle'];
				}
				unset($temp['othertitle']);

				$return[] = $temp;

			}
		}

		return $return;
	}

}
?>