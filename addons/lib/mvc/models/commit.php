<?php
namespace lib\mvc\models;
use \lib\debug;

trait commit
{
	/**
	 * [post_commit description]
	 * @param  [type] $_qry [description]
	 * @return [type]       [description]
	 */
	protected function post_commit($_qry)
	{
		$qry = $_qry->insert();
		// var_dump($_qry);exit();
		// ======================================================
		// you can manage next event with one of these variables,
		// commit for successfull and rollback for failed
		// if query run without error means commit
		$this->commit(function()
		{
			debug::true(T_("Insert Successfully"));
		} );

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			debug::title(T_("Transaction error").': ');
		} );
		return $qry->LAST_INSERT_ID();
	}

	/**
	 * [put_commit description]
	 * @param  [type] $_qry [description]
	 * @return [type]       [description]
	 */
	protected function put_commit($_qry)
	{
		$_qry = $_qry->update();
		// var_dump($_qry); exit();
		// ======================================================
		// you can manage next event with one of these variables,
		// commit for successfull and rollback for failed
		//
		// if query run without error means commit
		$this->commit(function()
		{
			debug::true(T_("Update Successfully"));
		} );

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			debug::title(T_("Transaction Error").': ');
		} );
	}

	/**
	 * [delete_commit description]
	 * @param  [type] $_qry [description]
	 * @return [type]       [description]
	 */
	protected function delete_commit($_qry)
	{
		$_qry = $_qry->delete();
		// var_dump($_qry);exit();
		// ======================================================
		// you can manage next event with one of these variables,
		// commit for successfull and rollback for failed
		//
		// if query run without error means commit
		$this->commit(function()
		{
			debug::true(T_("Delete Successfully"));
		} );

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			debug::error(T_("Delete Failed!"));
		} );
	}
}
?>
