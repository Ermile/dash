<?php
namespace lib;

class dbconnection
{
	private $converted_to_object       = false;
	private $record_is_called          = false;
	private $assoc_is_called           = false;
	private $save                      = false;
	private $allrecord                 = array();
	private $allassoc                  = array();
	private $allobject                 = array();
	private $i                         = 0;
	private $result                    = false;

	public $status                     = true;
	public $string                     = false;
	public $error                      = false;
	public $fieldNames                 = array();
	public $oFieldNames                = array();
	public static $connection          = false;
	public static $dbConnection        = array();
	public static $db_name_selected    = false;

	public static $db_name             = null;
	public static $db_user             = null;
	public static $db_pass             = null;
	public static $db_host             = 'localhost';
	public static $db_charset          = 'utf8';
	public static $db_lang             = 'fa_IR';


	public function __construct()
	{
		$cls = debug_backtrace();
		if(isset($cls[1]['class']) && $cls[1]['class'] == '\lib\sql')
		{
			$this->cls = $cls[1]['object'];
		}
		self::$db_name = self::$db_name ? self::$db_name : db_name;
		self::$db_user = self::$db_user ? self::$db_user : db_user;
		self::$db_pass = self::$db_pass ? self::$db_pass : db_pass;
		// self::$db_host = self::$db_host ? self::$db_host : db_host;
		if(!isset(self::$dbConnection[self::$db_name]))
		{
			// if mysqli class does not exist or have some problem show related error
			if(!class_exists('mysqli'))
			{
				echo( "<p>".T_("we can't find database service!")." "
							  .T_("please contact administrator!")."</p>" );
				\lib\main::$controller->_processor(array('force_stop' => true));
			}
			self::$connection = @new \mysqli(self::$db_host, self::$db_user, self::$db_pass, self::$db_name);


			if(self::$connection->connect_errno == 0 )
			{
				self::$connection->set_charset(self::$db_charset);
				self::$dbConnection[self::$db_name] = self::$connection;
			}
			else if(self::$connection->connect_errno == 1045 )
			{
				echo( "<p>".T_("We can't connect to database service!")." "
							  .T_("Please contact administrator!")."</p>" );
				\lib\main::$controller->_processor(['force_stop' => true]);
			}
			else if(self::$connection->connect_errno == 1049 )
			{
				// database does not exist, go to install page
				// echo( "<p>".T_("We can't connect to correct database!")." " .T_("Please contact administrator!")."</p>" );
				// if method exist, used for forms
				if(method_exists(\lib\main::$controller, 'redirector'))
				{
					\lib\main::$controller->redirector()->set_domain()->set_url('cp/install?time=first_time');
					if(\lib\dash::is_ajax())
					{
						\lib\main::$controller->_processor(['force_stop' => true, 'force_json' => true]);
					}
					else
					{
						\lib\main::$controller->_processor(['force_stop' => true, 'force_json' => false]);
					}
				}
				// on normal pages
				else
				{
					$redirector = new \lib\redirector();
					$redirector->set_domain()->set_url('cp/install?time=first_time')->redirect();
				}
			}
			else{
				$this->error(self::$connection->connect_error, self::$connection->connect_errno);
			}
		}
	}

	public static function get_db_name()
	{
		return self::$db_name ? self::$db_name : db_name;
	}

	public function query($string)
	{
		// if(self::$db_lang = 'fa_IR')
		// {
		// 	$patterns = array(
		// 		'/ة/',
		// 		'/إ/',
		// 		'/أ/',
		// 		'/ي/',
		// 		'/ئ/',
		// 		'/ؤ/',
		// 		'/ك/'
		// 	);
		// 	$replacements = array(
		// 		'ه',
		// 		'ا',
		// 		'ا',
		// 		'ی',
		// 		'ی',
		// 		'و',
		// 		'ک'
		// 	);
		// 	$string = preg_replace($patterns, $replacements, $string);
		// }

		// $patterns = array(
		// 	'/۰/',
		// 	'/۱/',
		// 	'/۲/',
		// 	'/۳/',
		// 	'/۴/',
		// 	'/۵/',
		// 	'/۶/',
		// 	'/۷/',
		// 	'/۸/',
		// 	'/۹/'
		// );
		// $replacements = array(
		// 	'0',
		// 	'1',
		// 	'2',
		// 	'3',
		// 	'4',
		// 	'5',
		// 	'6',
		// 	'7',
		// 	'8',
		// 	'9'
		// );
		// $string = preg_replace($patterns, $replacements, $string);
		if(debug::$status)
		{
			$this->string = $string;
			$this->result = self::$connection->query($string);
			if (self::$connection->error)
			{
				if(DEBUG)
				{
					\lib\db::log($string);
				}
				$this->status = false;
				$this->error(self::$connection->error, self::$connection->errno);
			}
		}
		return $this;
	}

	public function error($error = null, $errno = null)
	{
		$reg = new dberror();
		$f = "$errno";
		$aError = $reg->$f($error);
		$this->error = $aError;
		if(
			isset($this->cls)
			&& $this->error
			&& isset($this->cls->table)
			&& isset($this->cls->tables[$this->cls->table])
		)
		{
			$table = $this->cls->tables[$this->cls->table];
			$fieldName = $this->error['fieldname'];
			$errorName = isset($this->error['errorName']) ? $this->error['errorName'] : $this->error['errno'];
			if($table->{$fieldName}->closure->validate)
			{
				$error = isset($table->{$fieldName}->closure->validate->sql->{$errorName}) ? $table->{$fieldName}->closure->validate->sql->{$errorName} : false;
				if(DEBUG)
				{
					debug::error($error, $errorName, 'sql');
				}
				else
				{
					debug::error(T_('Error'), false, 'sql');
				}
			}
		}
		else
		{
			if(DEBUG)
			{
				debug::error($error, $errno, 'sql');
			}
			else
			{
				debug::error(T_('Error'), false, 'sql');
			}
		}
		return $aError;
	}

	public function result()
	{
		if($this->status)
		{
			return $this->result;
		}
		else
		{
			return false;
		}
	}

	public function save()
	{
		$this->save = true;
		return $this;
	}

	public function endSave()
	{
		$this->save = false;
		return $this->allrecord;
	}

	private function onSave($i, $result)
	{
		if ($this->save) {
			$this->allrecord[$i] = (array) $result; return $this;
		} else {
			return $result;
		}
	}

	private function _return($i, $field = null)
	{
		if (!is_int($i))
		{
			$field = $i;
			$i = $this->i;
		}
		$this->check_i($i);
		$ret = $this->record_is_called();
		if (!empty($this->allrecord[$this->i]))
		{
			if (gettype($field) == 'object')
			{
				$args   = func_get_args();
				$args   = array_splice($args, 2);
				array_unshift($args, $this->allrecord[$this->i]);
				$return = call_user_func_array($field, $args);
			}
			elseif ($field)
			{
				$return = $this->allrecord[$this->i][$field];
			}
			else
			{
				$return = $this->allrecord[$this->i];
			}
			$this->i++;
			return $this->onSave(($this->i - 1), $return);
		}
		else
		{
			return false;
		}
	}


	public function fieldNames()
	{
		$this->record_is_called();
		return $this->fieldNames;
	}

	public function oFieldNames()
	{
		$this->record_is_called();
		return $this->oFieldNames;
	}

	private function record_is_called()
	{
		if (!$this->record_is_called)
		{
			if ($this->result !== null)
			{
				$this->record_is_called = true;
				if(method_exists($this->result, "fetch_fields"))
				{
					$fields = $this->result->fetch_fields();
					$aFields = array();
					foreach ($fields as $key => $value)
					{
						if(array_search($value->name, $aFields) === false)
						{
							$this->oFieldNames[] = $value;
							$aFields[$key] = $value->name;
						}
					}
					$this->fieldNames = $aFields;
					while ($x = $this->result->fetch_array())
					{
						$record = array();
						foreach ($aFields as $key => $value)
						{
							$record[$value] = html_entity_decode($x[$key], ENT_QUOTES | ENT_HTML5, "UTF-8");
						}
						$this->allrecord[] = $record;
					}
				}
			}
		}
	}

	private function convert_to_array($object = null)
	{
		$all = array();
		foreach ($object as $key => $value)
		{
			$all[$key] = (array) $value;
		}
		return $all;
	}

	private function convert_to_object($array = null)
	{
		if (!$this->converted_to_object)
		{
			if (!$array)
			{
				$this->record_is_called();
				$array = $this->allrecord;
			}
			$all = array();
			foreach ($array as $key => $value)
			{
				$all[$key] = (object) $value;
			}
			$this->converted_to_object = true;
			$this->allobject = $all;
		}
		return $this->allobject;
	}

	private function check_i($i)
	{
		if ($i)
		{
			$this->i = $i;
		}
		if ($i < 0)
		{
			$this->i = 0;
		}
	}

	public function assoc($i = null, $field = null)
	{
		return call_user_func_array(array($this, "_return"), array($i, $field));
	}

	public function allAssoc($field = null)
	{
		$this->i = 0;
		$all = array();
		while ($x = $this->assoc($field))
		{
			$all[] = $x;
		}
		return ($this->save) ? $this : $all;
	}

	public function alist($i = null, $field = null)
	{
		$array = call_user_func_array(array($this, "_return"), array($i, $field));
		if(is_array($array))
		{
			return array_values($array);
		}
		else
		{
			return $array;
		}
	}

	public function allAlist($field = null)
	{
		$this->i = 0;
		$all = array();
		while ($x = $this->alist($field))
		{
			$all[] = $x;
		}
		return ($this->save) ? $this : $all;
	}

	public function object($i = null, $field = null) {
		$return = call_user_func_array(array($this, "_return"), array($i, $field));
		return ($return) ? (is_array($return)) ? (object) $return : $return  : false;
	}

	public function allObject($field = null) {
		$all = array();
		$this->i = 0;
		while ($x = $this->object($field)) {
			$all[] = $x;
		}
		return ($this->save) ? $this : $all;
	}

	public function string()
	{
		return $this->string;
	}

	public function num()
	{
		if ($this->status)
		{
			if(!$this->result)
			{
				return 0;
			}
			return $this->result->num_rows;
		}
		else
		{
			return false;
		}
	}

	public function LAST_INSERT_ID()
	{
		if ($this->status)
		{
			return self::$connection->insert_id;
		}
		else
		{
			return false;
		}
	}
}
?>