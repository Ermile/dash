<?php
namespace lib\utility;
class safe
{
	/**
	 * safe string for sql injection and XSS
	 * @param  string $_string unsafe string
	 * @return string          safe string
	 */
	public static function safe($_string, $_remove_inject = null)
	{
		if(is_array($_string) || is_object($_string))
		{
			return self::walk($_string, $_remove_inject);
		}

		// check baby to not allow to harm yourself
		// \lib\baby::check($_string);
		// if uncomment above code we have problem on some codes

		if(
			gettype($_string) == 'integer' ||
			gettype($_string) == 'double' ||
			gettype($_string) == 'boolean' ||
			$_string === null
			)
		{
			return $_string;
		}
		if(is_string($_remove_inject))
		{
			switch ($_remove_inject)
			{
				case 'sqlinjection':
					$_remove_inject = ["'", '"', '\\\\\\', '`', '\*', "\\?", ';'];
					break;
			}
		}
		if(is_array($_remove_inject))
		{
			$_string = preg_replace("/\s?[" . join('', $_remove_inject) . "]/", "", $_string);
		}
		$string = htmlspecialchars($_string, ENT_QUOTES | ENT_HTML5);
		$string = addcslashes($string, '\\');
		return $string;
	}

	/**
	 * Nested function for walk array or object
	 * @param  array or object $_value unpack array or object
	 * @return array or object         safe array or object
	 */
	private static function walk($_value, $_remove_inject = null)
	{
		foreach ($_value as $key => $value)
		{
			if(is_array($value) || is_object($value))
			{
				if(is_array($_value))
				{
					$_value[$key] = self::walk($value, $_remove_inject);
				}
				elseif(is_object($_value))
				{
					$_value->$key = self::walk($value, $_remove_inject);
				}
			}
			else
			{
				if(is_array($_value))
				{
					$_value[$key] = self::safe($value, $_remove_inject);
				}
				elseif(is_object($_value))
				{
					$_value->$key = self::safe($value, $_remove_inject);
				}
			}
		}
		return $_value;
	}
}
?>