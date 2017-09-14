<?php
namespace addons\content_cp\notifications;

class controller extends \mvc\controller
{
	public $fields =
	[
		'id',
		'user_id',
		'user_idsender',
		'title',
		'content',
		'url',
		'read',
		'star',
		'status',
		'category',
		'createdate',
		'senddate',
		'deliverdate',
		'expiredate',
		'readdate',
		'gateway',
		'auto',
		'datemodified',
		'desc',
		'meta',
		'sort',
		'order',
		'search',
		'data',
	];

	public function _route()
	{

		\lib\permission::access('cp:transaction:notifications', 'block');

		$property                 = [];
		foreach ($this->fields as $key => $value)
		{
			$property[$value] = ["/.*/", true, $value];
		}

		$this->get(false, "list")->ALL(['property' => $property]);

	}
}
?>