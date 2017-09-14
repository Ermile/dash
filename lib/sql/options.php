<?php
namespace lib\sql;
class options
{
	public $validate = null;
	public $form = null;
	public function validate()
	{
		if(!$this->validate)
		{
			$this->validate = new \lib\validator\maker(func_get_args());
		}
		return $this->validate;
	}

	public function form($type = 'text')
	{
		$formL      = new \lib\form();
		$form       = $formL->make($type);
		$this->form = $form;
		$table      = $this->table;
		// if set label in database files, use that text for label
		if(isset($table->{$this->fieldName}->label))
		{
			$this->form->label($table->{$this->fieldName}->label);
		}
		if(!$this->validate){
			$this->validate = $form->validate();
		}
		return $form;
	}

	/**
	 * this function set child for form elements like select or datalist
	 * if first param contain array then create a custom list
	 * if first param contain string then create a relation with primary table form foreign key
	 * five type of child can call this function is below. each call can fill with
	 * - From List
	 * 1. enum value from database field 						| without parameter
	 * 2. custom list passed with array(custom enum)
	 * - From Table
	 * 3. foreign key with default setting 					| without parameter
	 * 5. custom value from custom table
	 * 4. foreign key with custom filter(where or groupby)
	 * @param [type] $_all		[create list with this condition array is use for custom list]
	 * @param [type] $_default [default value for set]
	 * @param [type] $_query   [one type of input from above desc]
	 *
	 * @example #2 for custom list with custom default value
	 * 	$this->setChild(array('a','b','c'), 'b');
	 * @example #4 for add filter to child
	 * 	$this->setChild(null, null, function($q){$q->whereGroup("academic");});
	 * @example #5 for custom value from custom table
	 * 	$this->setChild('provinces@id!province_name');
	 */
	public function setChild($_all = null, $_default = null, $_query = null)
	{
		$args  = func_get_args();
		$table = $this->table;
		$child = $table->{$this->fieldName}->type;
		$opt   = $this->splitor($child);
		$form  = $this->form;

		// ********************************************************************** From List
		// if enum or custom array create a custom list
		if($opt['type'] == 'enum' || $opt['type'] == 'set' || is_array($_all))
		{
			if(is_array($_all))
				$tmp_values = $_all;
			else
				$tmp_values = $opt['value'];

			foreach ($tmp_values as $key => $value)
			{
				$childs = $form->child()->value($value)->label($value)->id($value);
				if($opt['type'] == "set")
					$childs->name($form->attr['name'].'[]');

				if($opt['default'] === $value || $_default === $value)
				{
					if($form->attr['type'] === 'radio')
						$childs->checked("checked");
					else
						$childs->selected("selected");
				}
			}
		}
		// ********************************************************************** From Table
		// if foreign key or custom list form custom table
		elseif( isset($table->{$this->fieldName}->foreign) || is_string($_all) )
		{
			return;
			// foreign written by javad @hasan: check for conflict with haram
			// check for count of running this function with uncomment below line
			// var_dump("test"); // this line run n times

			if(is_string($_all))
				$field = $_all;
			else
				$field = $table->{$this->fieldName}->foreign;

			$options = $this->splitor($field);
			$default = $options['default'] ? $options['default'] : $options['value'];

			$order = "order".ucfirst($default);
			$sql   = new \lib\sql\maker();
			$oType = $options['type'];
			$query = $sql::$oType()->$order();
			if(is_object($_query))
				call_user_func_array($_query, array($query));
			else
				$query->$order();

			$query = $query->select();
			foreach ($query->allAssoc() as $value)
			{
				$tmp_val = $value[$options['value']];
				if($tmp_val == $opt['default'] || $tmp_val == $_default)
					$this->form->child()->value($tmp_val)->label($value[$default])->selected('selected');
				else
					$this->form->child()->value($tmp_val)->label($value[$default]);
			}
		}
	}


	private function splitor($string){
		preg_match("/^(.*)@([^\!]*)(\!(.*))?$/", $string, $split);
		if($split[1] == 'enum' || $split[1] == 'set'){
			$value = preg_split("/\s?,\s?/", $split[2]);
		}else{
			$value = $split[2];
		}
		return array("type"=> $split[1], "value" => $value, "default" => isset($split[4]) && !empty($split[4])? $split[4] : false);
	}
}
?>