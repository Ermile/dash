<?php
namespace lib;
class validator
{
	public $group, $status = true, $select, $error, $value, $type = 'error', $_functions = array(), $name, $validate, $onError;
	public static $save = array();

	/**
	 * [__construct description]
	 * @param [type] $value     [description]
	 * @param [type] $validator [description]
	 * @param string $group     [description]
	 */
	public function __construct($value, $validator, $group = 'public')
	{
		if(!isset(self::$save[$group]) || !is_array(self::$save[$group]))
		{
			self::$save[$group] = array();
		}
		if(is_array($value))
		{
			$this->name = $value[0];
			$this->value = $value[1];
		}
		else
		{
			$this->name = count(self::$save[$group]);
			$this->value = $value;
		}

		self::$save[$group][$this->name] = $this;
		$this->group		= $group;
		$this->validate	= $validator;
		$this->config();
	}


	/**
	 * [config description]
	 * @return [type] [description]
	 */
	final function config()
	{
		if(method_exists($this->validate, 'getFunctions') && $this->validate->getFunctions())
		{
			foreach ($this->validate->getFunctions() as $key => $args)
			{
				if(empty($args))
				{
					$args = array();
				}
				elseif(!is_array($args))
				{
					$args = array($args);
				}

				if(!is_object($args[0]))
				{
					error::page("validate inline extends $key not found");
				}

				$closure = \Closure::bind($args[0], $this);

				$onf = $this->status;
				$ret = call_user_func_array($closure, $args);
				if($ret == false || ($onf === true && $this->status == false))
				{
					$this->setError($key);
				}
			}
		}

	}


	/**
	 * set error message to show for user, this is only notify message
	 * @param [type] $key [description]
	 */
	final function setError($key)
	{
		if($this->status || $this->error === null)
		{
			$this->status = false;

			if(isset($this->validate->form->$key))
			{
				$this->error = $this->validate->form->$key;
			}
			else
			{
				$mylabel = $this->field_userFriendly($key, 'label');
				$this->error = T_("error in field").' '. T_($mylabel);
			}
		}
	}


	/**
	 * [compile description]
	 * @return [type] [description]
	 */
	final function compile()
	{
		if($this->status)
			return $this->value;
		else
		{
			$myname  = $this->field_userFriendly($this->name, 'name');
			debug::{$this->type}($this->error, $myname, $this->group);
			return (empty($this->onError))? false : $this->onError;
		}
	}


	/**
	 * [type description]
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	final function type($type)
	{
		$this->type = ($type = 'warn')? 'warn' : 'error';
	}


	/**
	 * [onError description]
	 * @param  [type] $onError [description]
	 * @return [type]          [description]
	 */
	final function onError($onError)
	{
		$this->onError = $onError;
	}


	/**
	 * [get_validate description]
	 * @param  [type] $group [description]
	 * @return [type]        [description]
	 */
	public static function get_validate($group = null)
	{
		return $group == null ? self::$save : self::$save[$group];
	}


	/**
	 * [__callStatic description]
	 * @param  [type] $name [description]
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public static function __callStatic($name, $args)
	{
		if($name == 'all')
		{
			return self::$save;
		}
		else
		{
			return count($args) > 0 ? self::$save[$name][$args] : self::$save[$name];
		}
	}


	/**
	 * change field name with condition and return new user friendly name
	 * @param  [type] $_fieldname [description]
	 * @param  string $_export    [description]
	 * @return [type]             [description]
	 */
	public static function field_userFriendly($_fieldname, $_export = 'name')
	{
		$_fieldname = mb_strtolower($_fieldname);

		// check for _ exist in name or not
		$tmp_pos = strpos($_fieldname, '_');
		if($tmp_pos)
		{
			$suffix = substr($_fieldname, $tmp_pos + 1);
			$prefix = substr($_fieldname, 0, $tmp_pos);

			// if is foreign key like user_id or permission_id
			// change it to user_ or permission_
			if($suffix === 'id')
			{
				$myname  = $prefix.'_';
				$mylabel = $prefix;
				$mytype  = 'foreign';
			}

			// if especial foreign key like user_id_customer
			// change it to user_customer
			elseif(substr($suffix, 0, 2) === 'id_')
			{
				$myname  = $prefix.'_'.substr($suffix, 3);
				$mylabel = $prefix.' '.substr($suffix, 3);
				$mytype  = 'foreign2';
			}

			// for normal field like user_firstname or user_gender
			// change it to firstname or gender
			else
			{
				$myname  = $suffix;
				$mylabel = $suffix;
				$mytype  = 'normal';
			}
		}
		// in field like id return id
		else
		{
			$myname   = $_fieldname;
			$mylabel  = $_fieldname;
			$mytype  = 'id';
		}

		$result = array('name' => $myname, 'label' => $mylabel, 'type' => $mytype );

		return $result[$_export];
	}
}
?>