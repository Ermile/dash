<?php
namespace lib\db;

class db_return_errors
{
	public static $errors = array();
	public static $included_errors = false;
	public static $include_errors = array();
	public static function get($_error_code)
	{
		if(!self::$included_errors)
		{
			$include_path = array();
			$file_lists = explode(PATH_SEPARATOR, get_include_path());
			krsort($file_lists);
			foreach ($file_lists as $key => $value) {
				$db_errors_file = join(DIRECTORY_SEPARATOR, [rtrim($value, '/'), 'lib', 'db', 'db_errors.php']);
				if(file_exists($db_errors_file))
				{
					self::db_load_errors($db_errors_file);
					self::$include_errors[] = $db_errors_file;
				}
			}
			self::$included_errors = true;
		}
		if(isset(self::$errors[$_error_code]))
		{
			return self::$errors[$_error_code];
		}
		return null;
	}

	private static function db_load_errors($_file_name)
	{
		require($_file_name);
		self::$errors = array_replace(self::$errors, $errors);
	}
}
?>