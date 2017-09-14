<?php
namespace lib\form;
class maker
{
	public $attr     = array();
	public $child    = array();
	public $label    = '';
	public $validate = null;
	public $elname   = null;


	/**
	 * [__construct description]
	 * @param string $_type [description]
	 */
	function __construct($_type = 'text')
	{
		$this->elname = $_type;
		$this->attr('type', $_type);
	}


	/**
	 * [child description]
	 * @param  boolean $_index [description]
	 * @return [type]          [description]
	 */
	function child($_index = false)
	{
		if($_index !== false)
		{
			return $this->child[$_index];
		}
		else
		{
			$child = array_push($this->child, new $this);
			return $this->child[$child-1];
		}
	}


	/**
	 * [validate description]
	 * @return [type] [description]
	 */
	function validate()
	{
		if(!$this->validate)
		{
			$validate = new \lib\validator\maker();
			$this->validate = $validate;
			return $validate;
		}
		else
		{
			return $this->validate;
		}
	}


	/**
	 * [filter description]
	 * @return [type] [description]
	 */
	function filter()
	{
		if(!$this->filter)
		{
			$filter = new filterMaker(func_get_args());
			$this->filter = $filter;
			return $filter;
		}
		else
		{
			if(count(func_get_args())> 0)
			{
				call_user_func_array(array($this->filter, 'extend'), func_get_args());
			}
			return $this->filter;
		}
	}


	/**
	 * [compile description]
	 * @param  boolean $_autoSet [description]
	 * @return [type]           [description]
	 */
	function compile($_autoSet = true)
	{
		$array = array();
		$array['attr'] = $this->attr;
		// var_dump($array['attr']);
		$array['label'] = !empty($this->label) ? T_($this->label) : $this->label;
		$array['child'] = array();
		$array['validate'] = $this->validate;
		$child = $this->child;
		foreach ($child as $key => $value)
		{
			// if(isset($value->attr['type']))
			// {
			// 	unset($value->attr['type']);
			// 	$is_child = true;
			// }

			array_push($array['child'], $value->compile());
		}
		// $blackChild = array('type');
		foreach ($array['child'] as $key => $value)
		{
			unset($array['child'][$key]['child']);
			unset($array['child'][$key]['validate']);
			// foreach ($blackChild as $K => $V) {
			// 	if(isset($array['child'][$key]['attr'][$V])){
			// 		unset($array['child'][$key]['attr'][$V]);
			// 	}
			// }
		}

		if($_autoSet === true)
		{
			// if user don't set name of element give it from *->make('name')* and set it as name
			if(!isset($array['attr']['name']) && $this->elname)
			{
				$array['attr']['name'] = $this->elname;
			}
			// if user don't set pl of element give it from *->label('Hi')* and set it as placeholder
			if(!isset($array['attr']['placeholder']) && $array['label'] && isset($array['attr']['type']) && $array['attr']['type']!='submit')
			{
				if(is_array($array['label']))
				{
					if(isset($array['label']['txt']))
					{
						$array['attr']['placeholder'] = $array['label']['txt'];
					}
				}
				else
				{
					$array['attr']['placeholder'] = $array['label'];
				}
				if(!empty($array['attr']['placeholder']))
				{
					$array['attr']['placeholder'] = gettext($array['attr']['placeholder']);
				}
			}
			// if user don't set id of element give it from *->name('username')* and set it as id
			if(!isset($array['attr']['id']) && isset($array['attr']['name']))
			{
				$array['attr']['id'] = $array['attr']['name'];
			}
		}

		if(count($array['child']) < 1)
		{
			unset($array['child']);
		}

		return $array;
	}


	/**
	 * [attr description]
	 * @param  [type] $_name  [description]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	function attr($_name, $_value = '')
	{
		$this->attr[$_name] = $_value;
		if(!$_value)
			unset($this->attr[$_name]);

		return $this;
	}

	/**
	 * [classname description]
	 * @param  [type] $class [description]
	 * @return [type]        [description]
	 */
	function classname($_class)
	{
		return $this->attr('class',$_class);
	}


	/**
	 * [pl description]
	 * @param  [type] $_placeholder [description]
	 * @return [type]              [description]
	 */
	function pl($_placeholder = null)
	{
		return $this->attr('placeholder',$_placeholder);
	}


	/**
	 * [label description]
	 * @param  [type] $_label [description]
	 * @return [type]        [description]
	 */
	function label($_label = null)
	{
		$this->label = $_label;
		return $this;
	}


	/**
	 * [elname description]
	 * @param  [type] $_elname [description]
	 * @return [type]          [description]
	 */
	function elname($_elname = null)
	{
		$this->elname = $_elname;
		return $this;
	}



	/**
	 * [addClass description]
	 * @param [type] $class [description]
	 */
	function addClass($_class)
	{
		if(!isset($this->attr['class']))
		{
			return $this->classname($_class);
		}
		$aClass = preg_split("/ /", $this->attr['class']);
		array_push($aClass, $_class);

		return $this->classname(join(" ", $aClass));
	}

	/**
	 * [removeClass description]
	 * @param  [type] $class [description]
	 * @return [type]        [description]
	 */
	function removeClass($_class)
	{
		if(!isset($this->attr['class']))
		{
			return $this;
		}
		$aClass = preg_split("/ /", $this->attr['class'],-1, PREG_SPLIT_NO_EMPTY);
		$index = array_search($_class, $aClass);
		if($index !== false)
		{
			unset($aClass[$index]);
		}
		return $this->classname(join(" ", $aClass));
	}


	/**
	 * [__call description]
	 * @param  [type] $_name  [description]
	 * @param  [type] $_value [description]
	 * @return [type]        [description]
	 */
	function __call($_name, $_value)
	{
		$_name = preg_replace("/^([a-zA-Z0-9]+)_([a-zA-Z0-9]+)$/", "$1-$2", $_name);
		$_name = preg_replace("/^([a-zA-Z0-9]+)__([a-zA-Z0-9]+)$/", "$1_$2", $_name);
		$this->attr[$_name] = (isset($_value[0]))? $_value[0] : "";
		return $this;
	}
}
?>