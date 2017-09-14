<?php
namespace lib\db;
class db_return
{
	public $return;

	public function is($_name, ...$_args)
	{
		if(!isset($_args[0]))
		{
			$_args[0] = true;
		}
		return $this->get($_name) == $_args[0];
	}

	private function set($_name, ...$_args)
	{
		if(count($_args) > 1)
		{
			if(!isset($this->return[$_name]))
			{
				$this->return[$_name] = array();
			}
			elseif(!is_array($this->return[$_name]))
			{
				$this->return[$_name] = [$this->return[$_name]];
			}
			$this->return[$_name][$_args[0]] = $_args[1];
		}
		else
		{
			$this->return[$_name] = $_args[0];
		}
		if($_name == 'error_code' && class_exists('\lib\db\db_return_errors'))
		{
			$this->set_message(\dash::lib_static('db')->db_return_errors()::get($_args[0]));
		}
	}

	public function get($_name, ...$_args)
	{
		if(!isset($this->return[$_name]))
		{
			return null;
		}
		if(count($_args) > 0)
		{
			if(!is_array($this->return[$_name]))
			{
				return null;
			}
			return $this->return[$_name][$_args[0]];
		}
		else
		{
			return $this->return[$_name];
		}
	}

	public function __call($_name, $_args)
	{
		if(preg_match("/^set_(.*)$/", $_name, $peroperty))
		{
			$this->set($peroperty[1], ...$_args);
			return $this;
		}
		elseif(preg_match("/^get_(.*)$/", $_name, $peroperty))
		{
			return $this->get($peroperty[1], ...$_args);
		}
		elseif(preg_match("/^is_(.*)$/", $_name, $peroperty))
		{
			return $this->is($peroperty[1], ...$_args);
		}
	}
}
?>