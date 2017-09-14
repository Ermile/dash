<?php
namespace lib\db;

/** words managing **/
class words
{
	/**
	 * this library work with words table
	 * v1.0
	 */
	public static $spam  = [];
	public static $words = [];

	/**
	 * insert new recrod in words table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert($_args)
	{

		$set = [];
		foreach ($_args as $key => $value)
		{
			if($value === null)
			{
				$set[] = " `$key` = NULL ";
			}
			elseif(is_int($value))
			{
				$set[] = " `$key` = $value ";
			}
			else
			{
				$set[] = " `$key` = '$value' ";
			}
		}
		$set = join($set, ',');
		$query = "INSERT IGNORE INTO words	SET	$set ";
		return \lib\db::query($query, '[tools]');
	}


	/**
	 * insert multy word in data base
	 *
	 * @param      string   $_words  The words
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function insert_multi($_words)
	{
		if(!$_words || !is_array($_words))
		{
			return false;
		}

		$_words = join($_words, "'),('");

		$query = "INSERT IGNORE INTO words	(words.word) VALUES	('$_words')	";

		return \lib\db::query($query, '[tools]');
	}


	/**
	 * get string and add to word table
	 *
	 * @param      <type>  $_string  The string
	 */
	public static function save($_string)
	{
		if(!$_string)
		{
			return false;
		}

		$words = self::to_array($_string);

		$exist_word = self::get($words, true);

		if(!$exist_word)
		{
			$exist_word = [];
		}

		$new_word   = array_diff($words, $exist_word);
		if(!empty($new_word))
		{
			return self::insert_multi($new_word);
		}
		return true;
	}


	/**
	 * get the list of word
	 *
	 * @param      <type>  $_words  The words
	 */
	public static function get($_words, $_only_words = false)
	{
		if(!$_words)
		{
			return [];
		}

		$_words = self::to_array($_words);

		$where = [];
		foreach ($_words as $key => $value)
		{
			$where[] = " words.word = '". $value. "' ";
		}

		$where = join($where, "OR");

		$fields = '*';
		if($_only_words)
		{
			$fields = "words.word AS 'word'";
		}
		$query = "SELECT $fields FROM words WHERE $where";
		if($_only_words)
		{
			$result = \lib\db::get($query,'word', false, '[tools]');
		}
		else
		{
			$result = \lib\db::get($query, null, false, '[tools]');
		}
		return $result;
	}


	/**
	 * update status of words
	 *
	 * @param      <type>  $_words   The words
	 * @param      <type>  $_status  The status
	 */
	public static function set_status($_words, $_status)
	{
		if(!$_words)
		{
			return false;
		}

		$_words = self::to_array($_words);

		$where = [];
		foreach ($_words as $key => $value)
		{
			$where[] = ' words.word = \''. $value. '\' ';
		}

		$where = join($where, "OR");

		$query = "UPDATE words SET words.status = '$_status' WHERE $where";
		return \lib\db::query($query, '[tools]');
	}


	/**
	 * check status of words
	 *
	 * @param      <type>   $_string  The string
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static  function check($_string, $_status = 'enable', $_return_result = false)
	{
		if(!$_string)
		{
			return true;
		}

		if($_status === null)
		{
			$_status = 'enable';
		}

		$words      = self::to_array($_string);
		$db_words   = self::get($words);
		$spam_words = [];

		if($db_words && is_array($db_words))
		{
			foreach ($db_words as $key => $value)
			{
				if($value['status'] != $_status)
				{
					$spam_words[$value['word']] = $value['status'];
				}
			}
		}
		else
		{
			$db_words = [];
		}


		self::$spam  = $spam_words;
		self::$words = array_column($db_words, 'status', 'word');

		if($_return_result)
		{
			if(empty($spam_words))
			{
				return self::$words;
			}
			return $spam_words;
		}

		if(empty($spam_words))
		{
			return true;
		}
		return false;
	}


	/**
	 * Sets the words in database and check exist words.
	 *
	 * @param      <type>  $_string  The string
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function save_and_check()
	{
		$args = func_get_args();
		self::save(...$args);
		return self::check(...$args);
	}


	/**
	 * Returns a array by split the string
	 *
	 * @param      <type>  $_string  The string
	 *
	 * @return     array   String representation of the object.
	 */
	private static function to_array($_string)
	{
		// $req = "/\s|\,|\n|\\.|\t|\-|\=|\:|\;|\'|\"|\?|\>|\<|\!|\@|\#|\%|\^|\&|\*|\(|\)|\+|\/|\||\{|\}|\[|\]|\`|\~|\،|\؛|\_/";

		$words = [];
		if(is_array($_string))
		{
			foreach ($_string as $key => $value)
			{
				if(is_array($value))
				{
					$array = self::to_array($value);
					foreach ($array as $k => $v)
					{
						array_push($words, $v);
					}
				}
				else
				{
					$split = explode(" ", $value);
					foreach ($split as $k => $text)
					{
						array_push($words, $text);
					}
				}
			}
		}
		else
		{
			$words = explode(" ", $_string);
		}
		$words = array_filter($words);
		$words = array_unique($words);
		return $words;
	}
}
?>