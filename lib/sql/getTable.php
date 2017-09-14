<?php
namespace lib\sql;

class getTable
{
	// set an array for use in future like display or view or model for creating query...
	public static function get($_table, $_db = db_name)
	{
		$mytable  = self::loadTableFields($_table, $_db);
		if($mytable)
		{
			$ret = array();
			foreach ($mytable as $key => $value)
			{
				$ret[$key]         = self::field_coder($key);
				$ret[$key]['null'] = $value->null=='YES'? true: false;
				$ret[$key]['show'] = $value->show=='YES'? true: false;

				if($ret[$key]['hide'])
				{
					// fields like id and date_modified does not show on nothing section
					$ret[$key]['table'] = null;
					$ret[$key]['query'] = null;
					$ret[$key]['form']  = null;
				}
				else
				{
					if($ret[$key]['null'])
					{
						// if field allow null then only hide on table and show on other section
						$ret[$key]['table'] = false;
						$ret[$key]['query'] = true;
						$ret[$key]['form']  = true;
					}
					else
					{
						// if field does not allow null then remove from form and show on other section
						$ret[$key]['table'] = true;
						$ret[$key]['query'] = true;
						$ret[$key]['form']  = null;
					}
				}
			}
			// var_dump($ret);
			return $ret;
		}
		return null;
	}


	// create the value for show in javascript or other use from algorithm
	// this function can code fieldnames like id, user_id, user_name, user_id_customer
	public static function field_coder($_field)
	{
		$rep_str    = null;
		$tmp_result = array();

		$tmp_result['label'] =  \lib\validator::field_userFriendly($_field, 'label');
		if($tmp_result['label'])
			$tmp_result['label'] = T_($tmp_result['label']);

		$tmp_result['value'] = \lib\validator::field_userFriendly($_field, 'name');
		$tmp_result['type']  = \lib\validator::field_userFriendly($_field, 'type');



		// hide certain field form future use
		if($_field === 'id' || $_field === 'date_modified')
			$tmp_result['hide'] = true;
		else
			$tmp_result['hide'] = false;


		return $tmp_result;
	}


	// set an array for use in future like display or view or model for creating query...
	public static function enumValues($_table, $_db = db_name)
	{
		$mytable  = self::loadTableFields($_table, $_db);
		if($mytable)
		{
			// var_dump($mytable->post_status);

			$ret = array();
			foreach ($mytable as $key => $value)
			{
				if(substr($value->type, 0, 5) === 'enum@')
				{
					$myfield = $key;
					if( substr($_table, 0, -1) === substr($key, 0, mb_strlen($_table)-1) )
						$myfield = substr($key, mb_strlen($_table));

					$myvalues                 = substr($value->type, 5);
					$mydefault                = null;
					$defpos                   = strpos($myvalues, '!');
					$ret[$myfield]['default'] = null;

					if($defpos)
					{
						$mydefault                = substr($myvalues, $defpos+1);
						$ret[$myfield]['default'] = $mydefault;
						$myvalues                 = substr($myvalues, 0, $defpos);
					}

					$ret[$myfield]['value'] = explode(",",$myvalues);
				}
			}
			return $ret;
		}
		return null;
	}


	// load table files and return the fields data
	private static function loadTableFields($name, $db_name = db_name)
	{
		$blackList = array("index", "foreign", "unique");
		$sName = "\\database\\{$db_name}\\{$name}";
		if(class_exists($sName))
		{
			$table_load = new $sName;
			foreach ($table_load as $key => $value)
			{
				if(!preg_grep("/^$key$/", $blackList))
				{
					$keys = array_keys($value);
					$values = array_values($value);
					$array = array();
					foreach ($keys as $k => $v)
					{
						if(is_int($v))
						{
							$keys[$k] = $values[$k];
							$values[$k] = true;
						}
					}
					$array = array_combine($keys, $values);
					$table_load->$key = (object) $array;
				}
			}
			return $table_load;
		}
		return null;
	}
}
