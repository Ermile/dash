<?php
namespace lib\mvc\models;

trait options
{
	/**
	 * read options table and fill pass needed value
	 * @param  string $_key  name of option
	 * @param  string $_type type of needed
	 * @return [type]        array or string depending on needed item
	 */
	public function sp_get_options()
	{
		$qry_result  = [];
		$qry_options = $this->sql()->table('options')
					->where('user_id', 'IS', 'NULL')
					->and('post_id', 'IS', "NULL")
					->and('cat', 'like', "'option%'")

					// ->groupOpen('g_status')
					// ->and('status', '=', "'enable'")
					// ->or('status', 'IS', "NULL")
					// ->or('status', "")
					// ->groupClose('g_status')
					->select()
					->allassoc();

		return $qry_options;
	}


	/**
	 * return list of exist permission in system
	 * @return [array] contain list of permissions
	 */
	public function permList($_status = false)
	{
		$permList = [];
		if($_status === false)
		{
			// $permList = \lib\utility\option::get('permissions', 'meta');
			return $permList;
		}

		// get list of permissions
		$qryPerm = $this->sql()->table('options')
			->where('user_id', 'IS', 'NULL')
			->and('post_id', 'IS', "NULL")
			->and('cat', 'permissions')
			->and('status',"enable");

		if($_status)
		{
			$qryPerm
			->groupOpen('g_status')
			->and('status', '=', "'enable'")
			->or('status', 'IS', "NULL")
			->or('status', "")
			->groupClose('g_status');
		}
		$qryPerm  = $qryPerm->select()->allassoc();
		foreach ($qryPerm as $row)
		{
			$permList[$row['key']] = $row['value'];
		}

		return $permList;
	}
}
?>
