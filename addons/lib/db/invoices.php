<?php
namespace lib\db;
use \lib\db;

class invoices
{
	public $invoice = [];
	public $invoice_detail = [];


	/**
	 * load invoice title and detail
	 *
	 * @param      <type>  $_invoice_id  The invoice identifier
	 * @param      <type>  $_user_id     The user identifier
	 */
	public static function load($_invoice_id, $_user_id)
	{
		if(!$_invoice_id || !is_numeric($_invoice_id) || !$_user_id || !is_numeric($_user_id))
		{
			return false;
		}

		$get_invoice = ['id' => $_invoice_id, 'user_id' => $_user_id, 'limit' => 1];
		$get_invoice = self::get($get_invoice);

		if(!isset($get_invoice['id']))
		{
			return false;
		}

		$get_invoice_detail = \lib\db\invoice_details::get(['invoice_id' => $get_invoice['id']]);

		$result = [];
		$result['invoice'] = $get_invoice;
		$result['invoice_detail'] = $get_invoice_detail;
		return $result;
	}


	/**
	 * Searches for the first match.
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function search($_search = null, $_options = [])
	{
		if(!is_array($_options))
		{
			$_options = [];
		}

		$default_options =
		[
			'search_field' => "invoices.title LIKE '%__string__%'",
		];
		$_options = array_merge($default_options, $_options);

		return \lib\db\config::public_search('invoices', $_search, $_options);
	}


	/**
	 * get the invoice
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get()
	{
		return \lib\db\config::public_get('invoices', ...func_get_args());
	}


	/**
	 * insert the new invoice
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function insert()
	{
		\lib\db\config::public_insert('invoices', ...func_get_args());
		return \lib\db::insert_id();
	}


	/**
	 * add invoice
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function add($_args)
	{
		$this->invoice = $_args;
		return $this;
	}


	/**
	 * Adds a child of invoice
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function add_child($_args)
	{
		$this->invoice_detail[] = $_args;
		return $this;
	}

	/**
	 * save invoice
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function save()
	{
		if(!isset($this->invoice['date']))
		{
			$this->invoice['date'] = date("Y-m-d H:i:s");
		}

		if(!isset($this->invoice['count_detail']))
		{
			$this->invoice['count_detail'] = count($this->invoice_detail);
		}

		$invoice_id = \lib\db\invoices::insert($this->invoice);

		foreach ($this->invoice_detail as $key => $value)
		{
			$value['invoice_id'] = $invoice_id;
			\lib\db\invoice_details::insert($value);
		}

		return $invoice_id;
	}


}
?>