<?php
namespace lib\view;

trait optimize
{
	public $form;
	public $forms;


	/**
	 * [createform description]
	 * @param  [type] $_name [description]
	 * @param  [type] $_type [description]
	 * @return [type]        [description]
	 */
	function createform($_name, $_type = null, $_child = false)
	{
		$this->data->extendForm = true;
		if(!$this->form)
		{
			$this->twig_macro('form');
			$this->form = new  \lib\form;
			$this->data->form = object();
		}

		$args = func_get_args();
		if(count($args) === 2)
		{
			$submit_value = T_('submit');

			if($_type == 'add')
			{
				$submit_value = T_('submit');
			}
			elseif($_type == 'edit')
			{
				$submit_value = T_('save');
			}
			elseif($_type == 'login')
			{
				$submit_value = T_('sing in');
			}
			elseif($_type == 'register')
			{
				$submit_value = T_('create an account');
			}
			elseif(!empty($_type))
			{
				if(!$_child)
				{
					$submit_value = $_type;
				}
			}

			array_push($args, $submit_value);
		}
		if(preg_match("/^\-(.*)$/", $_name, $arg_match)){
			$form = $this->data->form->{$arg_match[1]} = new  \lib\form;
		}else{
			$form = call_user_func_array(array($this->form, 'make'), $args);
			if(get_class($form) == 'lib\form' || preg_match("/cls\\\\form/", get_class($form)))
			{
				// if user want to create child form use the name of child
				if($_child)
				{
					$this->data->form->{$_type} = $form;
				}
				// else do in normal way
				else
				{
					preg_match("/^(@[^\.]+)*\.(.+)$/", $_name, $sName);
					$this->data->form->{$sName[2]} = $form;
				}

				// if type of form is edit then fill it with related data
				if($_type == 'edit')
				{
					$this->form_fill($form, $sName[2]);
				}
			}
		}

		return $form;
	}


	/**
	 * This function fill forms for edit and work automatically
	 * @param  [type] $_form  [description]
	 * @param  [type] $_table [description]
	 * @return [type]         [description]
	 */
	public function form_fill($_form, $_table = null)
	{
		if(is_array($_table))
		{
			$_datarow = $_table;
		}
		elseif(!$_table)
		{
			return false;
		}
		else
		{
			$_table   = $_table? $_table: $this->data->module;
			$_datarow = $this->model()->datarow($_table);
		}

		foreach ($_form as $key => $value)
		{
			$myValue = null;
			$oForm   = $_form->$key;
			// set value in all condition first check simple method
			if(isset($_datarow[$key]))
			{
				$myValue = $_datarow[$key];
			}
			else
			{
				// else get key2 value
				$key2 = substr($key, strpos($key, '_')+1);
				// if value exist with keyname
				if(isset($_datarow[$key2]))
				{
					$myValue = $_datarow[$key2];
				}
				// else if value exist in array value field with special name
				elseif(isset($_datarow['value']) && ($key2 === 'name' || $key2 === 'default'))
				{
					$myValue = $_datarow['value'];
				}
				// else if set meta give meta value
				elseif(isset($_datarow['meta']) && isset($_datarow['meta'][$key2]))
				{
					$myValue = $_datarow['meta'][$key2];
				}
			}

			// for radio and select add special status for checking this element
			if(
				$oForm->attr['type'] === "radio" ||
				$oForm->attr['type'] === "select"
				// || $oForm->attr['type'] == "checkbox"
				)
			{
				foreach ($oForm->child as $k => $v)
				{
					if($v->attr["value"] == $myValue)
					{
						if ($oForm->attr['type'] == "select")
						{
							$_form->$key->child($k)->selected("selected");
						}
						else
						{
							$v->checked("checked");
						}
					}
					else
					{
						$v->attr('checked', null);
						$v->attr('selected', null);
					}
				}
			}
			// for checkbox add checked status to element
			elseif($oForm->attr['type'] === "checkbox")
			{
				if($myValue === 'enable' || $myValue === 'on')
				{
					$oForm->checked("checked");

				}
			}
			// else for simple fields add default value
			else
			{
				// if value is array
				if(is_array($myValue))
				{
					// if key with name value exist set it
					if(isset($myValue['value']))
					{
						$myValue = $myValue['value'];
						$oForm->value($myValue);
					}
				}
				// else if it's simple text
				else
				{
					$oForm->value($myValue);
				}
			}
		}
		// add datarow to form for future use!
		$_form->datarow = $_datarow;
	}

	public function twig_file_exists($_filename)
	{
		$filename = trim($_filename, '/');
		foreach ($this->twig_include_path as $key => $value) {
			$value = rtrim($value, '/');
			$file = join('/', [$value, $filename]);
			if(file_exists($file))
			{
				return $file;
			}
		}
	}
	public function addons_config($_name, $_addons)
	{
		$this->data->addons_name[$_name] = true;
	}
}
?>