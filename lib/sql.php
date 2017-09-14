<?php
namespace lib;
class sql{
	use sql\validate;
	/**
	 * if is true validate from \database\table\fields array
	 * @var boolean
	 */
	public $auto_validate = true;

	static $tables;
	private $blackList = array("index", "foreign", "unique");
	public static $connection = false;

	/**
	 * @param [object] $maker object of sql maker for make string query
	 */
	public function __construct($maker = false){
		if(!self::$tables){
			self::$tables = (object) array();
		}
		if(is_object($maker)){
			if(class_exists('\cls\sql') && method_exists('\cls\sql', "config")) {
				sql_cls::config($maker);
			}
			$this->maker = $maker;
			self::$tables = (object) array();
			$this->loadTable();
		}
	}

	public function string($string){
		$connection = new dbconnection;
		$result = $connection->query($string);
		return $result;
	}

	/**
	 * [__call description]
	 * @param  [string] $name [virtual function name]
	 * @param  [array] $args [arguments of virtual function]
	 * @return [type]       [description]
	 */
	public function __call($name, $args)
	{
		$syntax = str_replace("String", '', $name)."Caller";
		if(method_exists($this, $syntax))
		{
			$string = $this->$syntax($args);
		}

		if(preg_match("/^(insert|delete|update|select)(String)?$/", $name))
		{
			if(class_exists('\cls\sql') && method_exists('\cls\sql', "call"))
			{
				sql_cls::call($this->maker, $name);
			}

			$this->groupby($string);
			$this->order($string);
			$string .= (count($this->maker->limit) > 0) ? " LIMIT ". join($this->maker->limit, ', ') : '';
			// echo "\n<pre>\n";
			// echo($string)."\n\n";
			// echo "\n</pre>\n";
			if(preg_match("/String$/", $name))
			{
				return $string;
			}
			else
			{
				$connection = new dbconnection;
				$result = $connection->query($string);
				return $result;
			}
		}
		else
		{
			return $string;
		}
	}

	/**
	 * this function analyse and add groupby to sql query
	 * for use it you can use like an example: $qry->groupbyUser_status();
	 *
	 * if you want to use a custom function like day, month or ... only pass it with @
	 * $this->sql()->tableUsers()->groupbyUser_status('@DAY');
	 * we automatically detect it and add this function to value for groupby it.
	 *
	 * @param  [type] &$string give query string and change in func
	 * @return [type]          change global value and don't have return
	 */
	public function groupby(&$string)
	{
		$agroup = array();
		if($this->maker->groupby){
			array_push($agroup, array($this->maker->table, $this->maker->groupby));
		}
		foreach ($this->maker->join as $key => $value) {
			if($value->groupby){
				array_push($agroup, array($value->table, $value->groupby));
			}
		}
		$s = '';
		$a = array();
		foreach ($agroup as $key => $value) {
			$table = $value[0];
			foreach ($value[1] as $k => $v) {
				$groupby_ = $this->oString($table, $v);

				$tmp_groupby = $this->maker->groupby;
				$tmp_groupby = $tmp_groupby? $tmp_groupby: array();
				$mykey       = array_search($v, $tmp_groupby);

				if(substr($mykey, 0,1) === '@')
					array_push($a, substr($mykey, 1).'('.$groupby_.')');
				else
					array_push($a, $groupby_);
			}
		}
		if(count($agroup) > 0){
			$string .= " GROUP BY ".join($a, ', ');
		}
	}

	public function order(&$string){
		$aorder = array();
		if($this->maker->order){
			foreach ($this->maker->order as $key => $value) {
				array_push($aorder, array($this->maker->table, $value));
			}
		}
		foreach ($this->maker->join as $key => $value) {
			if($value->order){
				foreach ($this->maker->order as $k => $v) {
					array_push($aorder, array($value->table, $v));
				}
			}
		}
		$array_string_order = array();
		foreach ($aorder as $key => $value) {
			$orderField = $this->oString($value[0], $value[1][0]);
			array_push($array_string_order, "$orderField {$value[1][1]}");
		}
		if($aorder){
			$string .= " ORDER BY ".join(', ', $array_string_order);
		}
	}

	/**
	 * function for make select query
	 * @return [string] string of select query
	 */
	public function selectCaller()
	{
		$string = "SELECT ";
		// if user want use replace
		if($this->maker->syntaxArgs)
		{
			$string .= $this->maker->syntaxArgs. ' ';
		}

		$fields = array();
		$tables = array();
		$mapField = array();
		$this->oField($this->maker, $fields, $mapField);
		$fAs = (is_array($this->maker->fields)) ? $this->maker->fields : array($this->maker->fields);
		foreach ($mapField as $key => $value) {
			if(isset($this->maker->fieldsAs) && $this->maker->fieldsAs[$value]){
				$fields[$key] .=  " AS ". $this->maker->fieldsAs[$value];
			}
		}
		array_push($tables, $this->maker->table);
		foreach ($this->maker->join as $key => $value) {
			array_push($tables, $value->table);
			$Scount = count($fields);
			$this->oField($value, $fields, $mapField);
			for ($Scount; $Scount < count($mapField); $Scount++){
				if(isset($value->fieldsAs) && $value->fieldsAs[$mapField[$Scount]]){
					$fields[$Scount] .=  " AS ". $value->fieldsAs[$mapField[$Scount]];
				}
			}
		}
		$tablse = array($this->maker->table);
		$string .= join($fields, ", ");
		$string .= " FROM ". $this->oString($this->maker->table);
		$string .= $this->join();
		if(count($this->maker->conditions) > 0){
			$string .= " WHERE".$this->condition($this->maker);
		}
		return $string;
	}


	public function insertCaller($_args)
	{
		// if user want use replace
		if($this->maker->syntaxArgs === 'REPLACE')
		{
			$string = "REPLACE INTO ";
		}
		else
		{
			$string = "INSERT {$this->maker->syntaxArgs} INTO ";
		}

		$string .= "`".$this->maker->table."`";
		$keys = array_keys($this->maker->set);
		$fKeys = array_keys($this->maker->set);
		foreach ($keys as $key => $value) {
			$keys[$key] = $this->oString($this->maker->table, $value);
		}

		$values = array_values($this->maker->set);
		foreach ($values as $key => $value)
		{
			// if you want to set null for field pass value as '#NULL'
			// default null value is removed from query string
			if(!$value || $value == "")
			{
				unset($values[$key]);
				unset($keys[$key]);
			}
			else
				$values[$key] = $this->oString($this->maker->table, $fKeys[$key] ,$value);
		}

		$string .= " (".join($keys, ", ").")";
		if(is_array($values) && isset($values[0]) && is_array($values[0]))
		{
			$mydata = array();

			// change row and field together for work with implode func
			for ($row=0; $row < count($values[0]); $row++)
			{
				$mydata[$row] = array();
				for ($field=0; $field < count($values) ; $field++)
					if(is_numeric($values[$field][$row]))
						$mydata[$row][$field] = $values[$field][$row];
					else
						$mydata[$row][$field] = "'". $values[$field][$row]. "'";

			}

			// create sql string
			foreach ($mydata as $index => $row)
			{
				if($index === 0)
					$string .= " VALUES (".join($row, ", ").")";
				else
					$string .= ", (".join($row, ", ").")";
			}
		}
		else
			$string .= " VALUES (".join($values, ", ").")";

		return $string;
	}

	public function updateCaller()
	{
		// if user want use replace
		if($this->maker->syntaxArgs === 'REPLACE')
		{
			$string = "REPLACE INTO ";
		}
		else
		{
			$string = "UPDATE {$this->maker->syntaxArgs} ";
		}
		$string .= "`".$this->maker->table."`";
		$keys = array_keys($this->maker->set);
		$fKeys = array_keys($this->maker->set);
		foreach ($keys as $key => $value)
		{
			$keys[$key] = $this->oString($this->maker->table, $value);
		}

		$values = array_values($this->maker->set);
		foreach ($values as $key => $value)
		{
			// if you want to set null for field pass value as '#NULL'
			// default null value is removed from query string
			if((!$value && $value != 0) || $value == "")
			{
				unset($values[$key]);
				unset($keys[$key]);
			}
			else
			{
				if(is_array($value))
				{
					$value = implode(',', $value);
				}

				$values[$key] = $keys[$key]. " = ".$this->oString($this->maker->table, $fKeys[$key] ,$value);
			}
		}
		$string .= " SET ". join($values, ", ");
		if(count($this->maker->conditions) > 0)
		{
			$string .= " WHERE".$this->condition($this->maker);
		}
		return $string;
	}

	public function deleteCaller(){
		$string = "DELETE {$this->maker->syntaxArgs} FROM ";
		$string .= "`".$this->maker->table."`";
		$string .= " WHERE".$this->condition($this->maker);
		return $string;
	}

	public function join()
	{
		$string = "";
		foreach ($this->maker->join as $key => $value)
		{
			$string .= " {$value->joinModel} JOIN ".$value->table." ON";
			$string .= $this->condition($value);
		}
		return $string;
	}

	/**
	 * [condition description]
	 * @param  [type] $maker [description]
	 * @return [type]        [description]
	 */
	public function condition($maker)
	{
		// var_dump($maker->conditions);
		$string = "";
		foreach ($maker->conditions as $key => $value)
		{
			if(isset($value[0])){
				foreach ($value as $ckey => $cvalue)
				{
					if($ckey == 0){
						$string .= $key != 0 ? " ".mb_strtoupper($cvalue["condition"])."(" : "(";
					}else{
						$string .= " ".mb_strtoupper($cvalue["condition"])." ";
					}
					$string .= $this->conditionString($cvalue, $maker->table);
				}
				$string .= ")";
				/**
				 *
				 */
			}
			else
			{
				$string .= $key != 0 ? " ".mb_strtoupper($value["condition"])." " : " ";
				$string .= $this->conditionString($value, $maker->table);
			}
		}
		return $string;
	}
	/**
	 * [conditionString description]
	 * @param  [type] $condition [description]
	 * @param  [type] $table     [description]
	 * @return [type]            [description]
	 */
	public function conditionString($condition, $table)
	{
		$string = "";
		if(preg_match("/^#(.*)$/", $condition['field'], $field))
		{
			if(mb_strtolower($condition['operator']) == "like")
			{
				$op = "";
				if(preg_match("/^%(.*)$/", $condition['value'], $v))
				{
					$condition['value'] = $v[1];
					$op .= "0";
				}
				if(preg_match("/^(.*)%$/", $condition['value'], $v))
				{
					$condition['value'] = $v[1];
					$op .= "1";
				}
				$val = $this->oString($table, $field[1], $condition['value'], false);
				if(preg_match("/0/", $op))
				{
					if(preg_match("/^'/", $val))
					{
						$val = preg_replace("/^'/", "'%", $val);
					}
					else
					{
						$val = "'%$val";
					}
				}
				if(preg_match("/1/", $op))
				{
					if(preg_match("/'$/", $val))
					{
						$val = preg_replace("/'$/", "%'", $val);
					}
					else
					{
						$val = "$val%'";
					}
				}
			}
			else
			{
				// if value is empty or null set it
				if(isset($condition['value']))
					$val = $this->oString($table, $field[1], $condition['value']);
				else
					$val = 'NULL';
			}

			// if value is set to null then use is keyword for creating sql query
			if($val == "'NULL'")
				$val = "NULL";

			if($val == 'NULL' || $val == 'NOT NULL')
				$string .= $this->oString($table, $field[1])." iS ". $val;
			else
				$string .= $this->oString($table, $field[1])." {$condition['operator']} ". $val;
		}
		else
		{
			$string .= "$condition[field] {$condition['operator']} $condition[value]";
		}
		return $string;
	}


	/**
	 * set optiomize of select fields
	 * @param  [object] $maker   [sql maker object]
	 * @param  [array] $aFields [array of fields]
	 */
	public function oField($maker, &$aFields, &$map)
	{
		$table = $maker->table;
		$fields = is_array($maker->fields) ? $maker->fields : array($maker->fields);
		// var_dump($fields);

		if(!isset($fields[0]) || $fields[0] == "*")
		{
			array_push($aFields, $this->oString($table, "*"));
			array_push($map, "*");
		}
		else
		{
			foreach ($fields as $key => $value)
			{
				if($value === false)
				{
					// user pass false in mysql field function
					// to dont use the fild. this helpful on join
					// and you dont need the fields of joined table
					// array_push($aFields, $this->oString($table, $value));
				}
				else
				{
					array_push($aFields, $this->oString($table, $value));
					array_push($map, $value);
				}
			}
		}
	}


	/**
	 * optimize sql table, fields and value
	 * @param  [string] $table [set table name]
	 * @param  [string] $field [set field name]
	 * @param  [string] $value [set value]
	 * @return [string]        [optimize of string]
	 * @example
	 * 	oSting(users)			return #users#
	 * 	oSting(users, id)		return #users.id#
	 * 	oSting(users, id, 150)	return #users.id 150#
	 */
	public function oString($table, $field = null, $value = null, $checkCondition = true)
	{
		if($value !== null)
		{
			$cInt = false;
			// for insert or update multiple row
			if(is_array($value))
			{
				// $value = implode(',', $value);
				// $value = json_encode($value, JSON_FORCE_OBJECT);
			}
			elseif(preg_match("/^#(.*)$/", $value, $v))
			{
				$value = $v[1];
				$cInt = true;
			}
			// start with # without () like CASE ... When ...
			elseif( substr($value, 0, 1) == '#')
			{
				$value = substr($value, 1);
				$cInt = true;
			}
			else
			{
				$sTable = "get".ucfirst(dbconnection::get_db_name());
				$cTable = sql\table::$sTable($table);
				$atPos  = false;
				if(isset($cTable->$field))
				{
					$type = $cTable->$field->type;
					$int = array("int","tinyint", "smallint","decimal");
					preg_match("/^([^@]*)@/", $type, $tp);
					if(preg_grep("/^".$tp[1]."$/", $int))
					{
						$cInt = true;
					}
					if($this->auto_validate)
					{
						$status = $this->auto_validate($field, $cTable->$field, $value);
						if(!is_bool($status))
						{
							\lib\debug::error($status, $field, 'form');
						}
					}
				}
				if(isset($cTable->$field->closure) && $checkCondition)
				{
					$gTable = $cTable->$field->closure;
					$value = preg_replace("/^\\\#/", "#", $value);
					$v = new validator(array($field, $value), $gTable->validate, 'form');
					$value = $v->compile();
					$value = (($value == '' && is_string($value)) && ($value === false))? "NULL" : $value;


				}

				// switch by type of field and encode data if needed
				// var_dump($cTable->$field->type);

				if(isset($cTable->$field->type))
				{
					$atPos = strpos($cTable->$field->type, '@');
				}
				else
				{
					return false;
					// \lib\error::page("Field $field does not exist!");
				}
				if($atPos !== false)
				{
					switch(substr($cTable->$field->type, 0, $atPos))
					{
						// if the type of field is int do nothing
						case 'tinyint':
						case 'smallint':
						case 'mediumint':
						case 'int':
						case 'bigint':
						case 'decimal':
						case 'float':
							break;

						// else doing entities
						case 'tinytext':
						case 'text':
						case 'mediumtext':
						case 'longtext':

						default:
							// if does not contain meta doing nothing and encode value
							if(strpos($field, '_meta') === false)
							{
								$value = htmlentities($value, ENT_QUOTES, "UTF-8");
							}
							break;
					}
				}

				// if(!$cInt)
				// {
				// 	$value = htmlentities($value, ENT_QUOTES, "UTF-8");
				// }
			}
			if(is_array($value))
			{
				$optimize = $value;
			}
			else
				$optimize = $cInt ? "$value" : "'$value'";

		}
		else
		{
			$optimize = "`$table`";
			if($field)
			{
				if(preg_match("/^#/", $field))
				{
					$optimize = preg_replace("/^#/", "", $field);
				}
				else
				{
					// $optimize .= $field ? ($field === "*") ? ".$field" : ".`$field`" : "";
					if($field)
					{
						if(($field === "*"))
							$optimize .= ".$field";
						else
							$optimize .= ".`$field`";
					}
					else
					{
						$optimize .= "";
					}
				}
			}
		}
		return $optimize;

	}

	/**
	 * load ORM tables on this class
	 * change private $tables as array with $table index
	 */
	public function loadTable(){
		$tName = array($this->maker->table);
		foreach ($this->maker->join as $key => $value) {
			array_push($tName, $value->table);
		}
		foreach ($tName as $key => $value) {
			// \sql\table::($value);
		}
	}

	public function getForms($index = 0){
		$tab = $this->maker->table;
		$table = self::$tables->$tab;
		return $table;
	}
}
?>