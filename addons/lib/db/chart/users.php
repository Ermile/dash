<?php
namespace lib\db\chart;

/** get users stats **/
class users
{
	/**
	 * this library generate stats from users
	 * v1.2
	 */


	/**
	 * generate signup stats by custom period
	 * @param  string $_period [description]
	 * @return [type]          [description]
	 */
	public static function signup($_period)
	{
		if(!$_period)
		{
			$_period = "%Y-%m";
		}
		$qry ="SELECT
				DATE_FORMAT(datecreated, '$_period') as date,
				count(id) as total
			FROM users
			WHERE datecreated != 0
			GROUP BY date
		";

		$result = \lib\db::get($qry);
		return $result;
	}
}
?>