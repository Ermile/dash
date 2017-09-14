<?php
namespace addons\content_cp\transactions;

class controller extends \mvc\controller
{
	public $fields =
	[
		'id',
		'user_id',
		'code',
		'title',
		'type',
		'unit_id',
		'date',
		'time',
		'amount_request',
		'amount_end',
		'plus',
		'minus',
		'budget_before',
		'budget',
		'status',
		'condition',
		'verify',
		'parent_id',
		'related_user_id',
		'related_foreign',
		'related_id',
		'payment',
		'payment_response',
		'meta',
		'desc',
		'createdate',
		'datemodified',
		'mobile',
		'displayname',
		'unit',
		'order',
		'sort',
		'search',
	];

	public function _route()
	{

		\lib\permission::access('cp:transaction', 'block');

		$property                 = [];

		foreach ($this->fields as $key => $value)
		{
			$property[$value] = ["/.*/", true , $value];
		}

		$this->get(false, "list")->ALL(['property' => $property]);

	}
}
?>