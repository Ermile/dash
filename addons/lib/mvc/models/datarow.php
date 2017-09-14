<?php
namespace lib\mvc\models;

trait datarow
{
	// this function get table name and id then related record of it. table name and id can set
	// but if user don't pass table name or id,
	// function use current real method name
	// get it from url for table name and current parameter for id
	public function datarow($_table = null, $_id = null, $_metatable = false)
	{
		if (!$_table)
			$_table = $this->module();

		// if myid parameter set use it else use url parameter for myid
		if (!$_id)
			$_id    = $this->childparam();

		$tmp_result = $this->sql()->table($_table)->where('id', $_id)->select();

		if ($tmp_result->num() == 1)
		{
			$tmp_result = $tmp_result->assoc();
			// add meta table rows as filed to datarow, can access via meta in datarow
			if($_metatable)
			{
				$metas  = $this->sql()->table('options')->where('post_id', $_id)
					->field('key', 'value', 'meta')->select()->allassoc();
				foreach ($metas as $key => $value)
				{
					$myval = $value['meta'];
					if(substr($myval, 0,1) === '{')
						$myval = json_decode($myval, true);
					$mykey = strtok($value['key'], '_');
					$tmp_result['meta'][$mykey] = $myval;
				}
			}

			return $tmp_result;
		}

		elseif($tmp_result->num() > 1)
			\lib\error::access(T_("id is found 2 or more times. it's imposible!"));

		else
		{
			\lib\error::access(T_("Url incorrect: id not found"));
			return false;
		}

		return null;
	}
}
?>
