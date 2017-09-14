<?php
namespace lib\utility\location;

class tld
{

	public static $data =
	[
		'ir'  => ['name' => 'ir', 'lang' => 'fa'],
		'com' => ['name' => 'com', 'lang' => 'en']
	];


	/**
	 * get lost of tld
	 */
	public static function list($_request = null, $_index = null)
	{
		if($_request === null)
		{
			return self::$data;
		}
		else
		{
			if($_index === null)
			{
				return array_column(self::$data, $_request);
			}
			else
			{
				return array_column(self::$data, $_index, $_request);
			}
		}
	}


	/**
	 * check duage exist and return true or false
	 * @param  [type] $_tld   [description]
	 * @param  string $_column [description]
	 * @return [type]          [description]
	 */
	public static function check($_tld, $_column = 'name')
	{
		$tld_list = array_column(self::$data, $_column);
		if(in_array($_tld, $tld_list))
		{
			return true;
		}
		return false;
	}


	/**
	 * get tld
	 *
	 * @param      <type>  $_key      The key
	 * @param      string  $_request  The request
	 */
	public static function get($_key = Tld, $_request = 'lang')
	{
		$result = null;
		if(isset(self::$data[$_key]))
		{
			$result = self::$data[$_key][$_request];
		}

		return $result;
	}
}
?>