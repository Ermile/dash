<?php
namespace lib;
/**
 * some check for baby to not allow to harm yourself
 * v1.1
 */
class baby
{
	private static $level;
	/**
	 * add function to full check url and all user parameters
	 * @return block baby if needed
	 */
	public static function block()
	{
		// if we dont have request url it was very mysterious, say Hi to hitler
		if(!isset($_SERVER['REQUEST_URI']))
		{
			\lib\error::bad('Hi Father!');
		}

		// check request uri
		self::check($_SERVER['REQUEST_URI'], true);

		// check for requests
		foreach ($_REQUEST as $key => $value)
		{
			// check key is not using invalid chars
			self::check($key, true);

			if(is_array($value))
			{
				foreach ($value as $key2 => $value2)
				{
					// check key2 is not using invalid chars
					self::check($key2, true);
					if(is_array($value2))
					{
						// now we are not allow to give object in array request
						self::$level = 20;
						self::pacifier();
					}
					else if(is_object($value2))
					{
						// now we are not allow to give object in array request
						self::$level = 11;
						self::pacifier();
					}
					else
					{
						// self::check($value2, true);
					}
				}
			}
			else if(is_object($value))
			{
				// now we are not allow to give object in request
				self::$level = 10;
				self::pacifier();
			}
			else
			{
				// self::check($value, true);
			}
		}
		// we can add some check on php://input and maybe another one!
	}

	private static function pacifier()
	{
		$msg = 'Hi Baby'. str_repeat('!', self::$level);
		if(\lib\dash::is_json_accept() || \lib\temp::get('api') || \lib\dash::is_ajax())
		{
			\lib\error::bad($msg. ' Are you healthy?');
		}
		else
		{
			\lib\error::bad($msg);
		}
		self::$level = null;
	}

	/**
	 * check input text to have problem with hex or invalid chars
	 * @param  [type]  $_txt       [description]
	 * @param  boolean $_onlyCheck [description]
	 * @return [type]              [description]
	 */
	public static function check($_txt, $_block = false)
	{
		$result = null;
		// decode url
		$_txt = urldecode($_txt);
		// check for problem in hex
		if(self::hex($_txt))
		{
			$result = true;
		}
		// check for problem for containing forbidden chars
		else if(self::forbidden($_txt))
		{
			$result = true;
		}
		// if needed block
		if($result === true && $_block)
		{
			self::pacifier();
		}
		// return final result if not blocked!
		return $result;
	}


	/**
	 * check some problem on hexas input or someother things
	 * @param  [type] $_txt [description]
	 * @return [type]       [description]
	 */
	public static function hex($_txt)
	{
		if(preg_match("#0x#Ui", $_txt))
		{
			self::$level = 1;
			return true;
		}
		if(preg_match("#0x#", $_txt))
		{
			self::$level = 2;
			return true;
		}
		// if cant find something return false
		return false;
	}


	/**
	 * check for using forbiden char in txt
	 * @param  [type]  $_txt            [description]
	 * @param  [type]  $_forbiddenChars [description]
	 * @return boolean                  [description]
	 */
	public static function forbidden($_txt, $_forbiddenChars = null)
	{
		if(!$_forbiddenChars || !is_array($_forbiddenChars))
		{
			$_forbiddenChars = ['"', "`" , "'", ';', '%', '*', '\\'];
		}
		foreach ($_forbiddenChars as $name)
		{
			if (stripos($_txt, $name) !== FALSE)
			{
				self::$level = 3;
				return true;
			}
		}
	}

}
?>