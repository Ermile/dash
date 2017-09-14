<?php
namespace addons\content_cp\logs;

class controller extends \mvc\controller
{
	public $fields =
	[
		'id',
		'logitem_id',
		'type',
		'caller',
		'title',
		'priority',
		'user_id',
		'data',
		'status',
		'createdate',
		'datemodified',
		'sort',
		'order',
		'search',
		'data',
	];

	public function _route()
	{

		\lib\permission::access('cp:transaction:logs', 'block');

		$property                 = [];
		foreach ($this->fields as $key => $value)
		{
			$property[$value] = ["/.*/", true, $value];
		}

		$this->get(false, "list")->ALL(['property' => $property]);

	}
}
?>