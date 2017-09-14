<?php
namespace lib\view;

class data
{
	public function _toArray()
	{
		$toArray = array();
		if(method_exists($this, 'toArray')) $this->toArray();
		if(isset($this->form))
		{
			$this->form = (array) $this->form;
			foreach ($this->form as $key => $value)
				$this->form[$key] = $value->compile();
		}
		return (array) $this;
	}
}
?>