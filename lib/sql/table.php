<?php
namespace lib\sql;

class table
{
	public static $select;
	public static $tables = array();
	public function __construct(){
		call_user_func_array('self::load', func_get_args());
	}

	public static function load()
	{
		$args = func_get_args();
		$db_name = $args[0];
		$tables = $args[1];
		$return = array();
		foreach ($tables as $key => $value)
		{
			if(!isset(self::$tables[$db_name])) self::$tables[$db_name] = array();
			if(isset(self::$tables[$db_name][$value]))
			{
				$return[] = self::$tables[$db_name][$value];
				continue;
			}
			self::$tables[$db_name][$value] = $return[] = self::loadTable($value, $db_name);
		}
		return func_num_args() == 2 ? $return[0] : $return;
	}

	public static function loadTable($name, $db_name = db_name)
	{
		if(isset(self::$tables[$db_name][$name])) return self::$tables[$db_name][$name];
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
					if(method_exists($table_load, $key))
					{
						$options = new \lib\sql\options;
						$func = new \ReflectionMethod($sName, $key);
						$Closure = $func->getClosure($table_load);
						$options->$key = @\Closure::bind($Closure, $options);

						$options->table = $table_load;
						$options->tableName = $table_load;
						$options->fieldName = $key;
						$values[] = $options;
						$keys[] = 'closure';
					}
					$array = array_combine($keys, $values);
					$table_load->$key = (object) $array;
				}
			}
			foreach ($table_load as $key => $value)
			{
				if(method_exists($table_load, $key))
				{
					if(isset($table_load->{$key}->closure))
					{
						$closure = $table_load->{$key}->closure;
						if($closure->$key)
						{
							@call_user_func($closure->$key);
						}
					}
				}
			}
			self::$tables[$db_name][$name] = $table_load;
			return $table_load;
		}
		return null;
	}

	static function __callStatic($name, $args)
	{
		if(preg_match("/^get([A-Z].*)$/", $name, $db))
		{
			return call_user_func_array('self::load', array(mb_strtolower($db[1]), $args));
		}
	}
}
?>