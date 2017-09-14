<?php
namespace lib\mvc\controllers;
use \lib\router;

trait url
{
	/**
	 * return module name for use in view or other place
	 * @param  [type]  $_type [description]
	 * @param  boolean $_fix  [description]
	 * @return [type]         [description]
	 */
	public function module($_type = null, $_fix = true)
	{
		if($_type == 'prefix')
			$mymodule	= substr(router::get_url(0), 0, -1);
		elseif($_type == 'array')
			$mymodule	= router::get_url(-1);
		else
			$mymodule	= router::get_url(0);

		if($_fix)
			$mymodule	= $mymodule? $mymodule: 'home';

		return $mymodule;
	}


	/**
	 * return module name for use in view or other place
	 * @param  [type] $_title [description]
	 * @return [type]         [description]
	 */
	public function child($_title = null)
	{
		$mychild = router::get_url(1);
		if(strrpos($mychild,'=') !== false)
		{
			$mychild = substr($mychild,0,strrpos($mychild,'='));
		}

		if(!$_title)
			return $mychild;

		if($mychild=='add')
			return T_('add new');

		if($mychild == 'edit')
			return T_('edit');

		if($mychild == 'delete')
			return T_('delete');

	}


	/**
	 * if pass parameter return the property of it, else return value of child
	 * @param  [type] $_name [description]
	 * @return [type]        [description]
	 */
	public function childparam($_name = null)
	{
		if($_name)
			return router::get_url_property($_name);
		else
			return router::get_url_property($this->child());
	}
}
?>