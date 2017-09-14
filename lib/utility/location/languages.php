<?php
namespace lib\utility\location;
/** country managing **/
class languages
{

	public static $data =
	[
		'en' => ['name' => 'en', 'direction' => 'ltr', 'iso' => 'en_US', 'localname' => 'English', 'country' => ['United Kingdom', 'United States']],
		'fa' => ['name' => 'fa', 'direction' => 'rtl', 'iso' => 'fa_IR', 'localname' => 'Persian - فارسی', 'country' => ['Iran']],
		'ar' => ['name' => 'ar', 'direction' => 'rtl', 'iso' => 'ar_SU', 'localname' => 'Arabic - العربية', 'country' => ['Saudi Arabia']],
	];


	/**
	 * get lost of languages
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
	 * check language exist and return true or false
	 * @param  [type] $_lang   [description]
	 * @param  string $_column [description]
	 * @return [type]          [description]
	 */
	public static function check($_lang, $_column = 'name')
	{
		$lang_list = array_column(self::$data, $_column);
		if(in_array($_lang, $lang_list))
		{
			return true;
		}
		return false;
	}


	/**
	 * get lang
	 *
	 * @param      <type>  $_key      The key
	 * @param      string  $_request  The request
	 */
	public static function get($_key, $_request = 'iso')
	{
		$result = null;
		// if pass more than 2 character, then only use 2 char
		if(strlen($_key)> 2)
		{
			$_key = substr($_key, 0, 2);
		}
		if(!empty(self::$data) && isset(self::$data[$_key]))
		{
			if($_request === 'all' || !$_request)
			{
				$result = self::$data[$_key];
			}
			else
			{
				$result = self::$data[$_key][$_request];
			}
		}
		return $result;
	}


	/**
	 * return list of languages in current project
	 * read form folders exist in includes/languages
	 * @return [type] [description]
	 */
	public static function languages($_dir = false)
	{
		// detect languages exist in current project
		$langList = glob(dir_includes.'languages/*', GLOB_ONLYDIR);
		$myList   = ['en' => 'English'];
		foreach ($langList as $myLang)
		{
			$myLang     = preg_replace("[\\\\]", "/", $myLang);
			$myLang     = substr( $myLang, (strrpos($myLang, "/" )+ 1));
			$myLang     = substr($myLang, 0, 2);
			$myLangName = $myLang;
			$myLangDir  = 'ltr';
			switch (substr($myLang, 0, 2))
			{
				case 'fa':
					$myLangName = 'Persian - فارسی';
					$myLangDir  = 'rtl';
					break;

				case 'ar':
					$myLangName = 'Arabic - العربية';
					$myLangDir  = 'rtl';
					break;

				case 'en':
					$myLangName = 'English';
					$myLangDir  = 'ltr';
					break;

				case 'de':
					$myLangName = 'Deutsch';
					break;


				case 'fr':
					$myLangName = 'French';
					break;
			}
			$myList[$myLang] = $myLangName;
		}

		if($_dir)
		{
			return $myLangDir;

		}
		return $myList;
	}
}
?>