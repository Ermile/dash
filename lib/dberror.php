<?php
/**
*/
namespace lib;
class dberror {
	function __call($errno, $error)
	{
		$f = "error_$errno";
		if(method_exists($this, $f))
		{
			return $this->$f($error[0], $errno);
		}
		else
		{
			$return				= array();
			$return['errno']	= $errno;
			$return['error']	= $error;
			return $return;
			// var_dump($error, 10);
		}

	}

	function error_1452 ($error = null, $errno = 0) {
		$rep1                = preg_replace("/Cannot add or update a child row: a foreign key constraint fails|REFERENCES|FOREIGN KEY|CONSTRAINT|\'|\`|\(|\)|\,|\-/", "", $error);
		$rep2                = preg_replace("/\s{2}/", " ", $rep1);
		$rep3                = preg_replace("/\s{2}/", " ", $rep2);
		$array               = preg_split("[\s|\.]", $rep3);
		$return              = array();
		$return['errno']     = $errno;
		$return['database']  = $array[1];
		$return['table']     = $array[2];
		$return['fk']        = $array[3];
		$return['fieldname'] = $array[4];
		$return['fromTable'] = $array[5];
		$return['fromField'] = $array[6];
		return $return;
	}

	function error_1062 ($error = null, $errno = 0) {
		$rep1                  = preg_replace("/\'|\`|\(|\)|\,|/", "", $error);
		$rep2                  = preg_match("/^Duplicate entry\s(.*)\sfor key\s(.*)$/", $rep1, $array);
		$return                = array();
		$return['errno']       = $errno;
		$return['values']      = $array[1];
		$return['fieldname']   = $array[2];
		$return['stringError'] = $array[0];
		$return['errorName']   = "unique";
		return $return;
	}
}
?>