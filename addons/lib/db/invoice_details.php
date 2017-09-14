<?php
namespace lib\db;
use \lib\db;

class invoice_details
{

	/**
	 * get the invoice
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get()
	{
		return \lib\db\config::public_get('invoice_details', ...func_get_args());
	}


	/**
	 * insert the new invoice
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function insert()
	{
		\lib\db\config::public_insert('invoice_details', ...func_get_args());
		return \lib\db::insert_id();
	}


	/**
	 * Searches for the first match.
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function search()
	{
		return \lib\db\config::public_search('invoice_details', ...func_get_args());
	}
}
?>