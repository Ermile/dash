<?php
namespace lib\db;

trait pagination
{
	/**
	 * get pagnation
	 *
	 * @param      <type>  $_query   The query
	 * @param      <type>  $_length  The length
	 *
	 * @return     <type>  array [startlimit, endlimit]
	 */
	public static function pagnation($_query, $_length, $_force = true)
	{
		if($_force)
		{
			if(is_int($_query))
			{
				$count = $_query;
			}
			else
			{
				$count = self::query($_query);
				if($count && is_a($count, "mysqli_result"))
				{
					$count = mysqli_num_rows($count);
				}
				else
				{
					$count = 0;
				}
			}
			\lib\main::$controller->pagnation_make($count, $_length);
			$current = \lib\main::$controller->pagnation_get('current');

			$length = \lib\main::$controller->pagnation_get('length');
			$limit_start = ($current - 1) * $length ;
			if($limit_start < 0)
			{
				$limit_start = 0;
			}
			$limit_end = $length;
			return [$limit_start, $limit_end];
		}
	}
}
?>