<?php
namespace lib\db;

trait get
{
	/**
	 * run query and get result of this query
	 * @param  [type]  $_qry          [description]
	 * @param  [type]  $_column       [description]
	 * @param  boolean $_onlyOneValue [description]
	 * @return [type]                 [description]
	 */
	public static function get($_qry, $_column = null, $_onlyOneValue = false, $_db_name = true)
	{
		// generate query and get result
		$result = self::query($_qry, $_db_name);
		// fetch datatable by result
		$result = self::fetch_all($result, $_column);
		// if we have only one row of result only return this row
		if($_onlyOneValue && count($result) === 1)
		{
			if(isset($result[0]))
			{
				$result = $result[0];
			}
			else
			{
				$result = null;
			}
		}
		return $result;
	}


	/**
	 * fetch all row from database
	 * @param  [type] $_result    [description]
	 * @param  [type] $resulttype [description]
	 * @return [type]             [description]
	 */
	public static function fetch_all($_result, $_field = null, $resulttype = MYSQLI_ASSOC)
	{
		$result = [];
		// if mysqli fetch all is exist use it
		if(is_a($_result, 'mysqli_result'))
		{
			if(function_exists('mysqli_fetch_all'))
			{
				$result = @mysqli_fetch_all($_result, $resulttype);
			}
			else
			{
				for (; $tmp = $_result->fetch_array($resulttype);)
				{
					$result[] = $tmp;
				}
			}
		}

		// give only one column of result
		if($result && $_field !== null)
		{
			if(is_array($_field))
			{
				// if pass 2 field use one as key and another as value of result
				if(count($_field) === 2 && isset($_field[0]) && isset($_field[1]))
				{
					$result_key   = array_column($result, $_field[0]);
					$result_value = array_column($result, $_field[1]);
					if($result_key && $result_value)
					{
						// for two field use array_combine
						$result = array_combine($result_key, $result_value);
					}
				}
				else
				{
					// need more than 2 field
				}

			}
			else
			{
				$result = array_column($result, $_field);
			}
		}
		// return result
		return $result;
	}


	/**
	 * create select query if you can't create manually!
	 * @param  string $_table [description]
	 * @param  array  $_field [description]
	 * @param  array  $_where [description]
	 * @param  array  $_arg   [description]
	 * @return [type]         [description]
	 */
	public static function select($_table = 'options', $_field = [], $_where = [], $_arg = [])
	{
		// calc fields
		$myfield = "*";
		if(is_array($_field) & $_field)
		{
			$myfield = implode(", ", $_field);
			$myfield = substr($myfield, 0, -2);
		}
		elseif(isset($_field))
		{
			$myfield = "`$_field`";
		}

		// calc where
		$mywhere = "";
		if(is_array($_where) & $_where)
		{
			foreach ($_where as $key => $value)
			{
				// in all condition except first loop
				if($mywhere)
				{
					$opr = 'AND';
					// if opr isset use it
					if(isset($value['opr']))
					{
						$opr = $value['opr'];
						if(isset($value['value']))
						{
							$value = $value['value'];
						}
						else
						{
							// if value is not set use null
							$value = "NULL";
						}
					}
					$mywhere .= " $opr ";
				}

				if(is_array($value))
				{
					$value = implode(", ", $value);
					$value = substr($value, 0, -2);
					$mywhere .= "$key IN ($value)";
				}
				elseif(substr($value, 0, 4) === 'LIKE')
				{
					$mywhere .= "`$key` $value";
				}
				elseif(is_string($value))
				{
					$mywhere .= "`$key` = '$value'";
				}
				else
				{
					$mywhere .= "`$key` = $value";
				}
			}
		}
		else
		{
			$mywhere = "";
		}


		$qry = "SELECT $myfield FROM $_table";
		if($mywhere)
		{
			$qry .= " WHERE $mywhere";
		}

		return $qry;
	}
}
?>