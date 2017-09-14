<?php
namespace lib\mvc;

class model extends \lib\model
{
	use \lib\mvc\models\account;
	use \lib\mvc\models\breadcrumb;
	use \lib\mvc\models\commit;
	use \lib\mvc\models\cp;
	use \lib\mvc\models\datarow;
	use \lib\mvc\models\nav;
	use \lib\mvc\models\options;
	use \lib\mvc\models\posts;
	use \lib\mvc\models\template;
	use \lib\mvc\models\terms;
	use \lib\mvc\models\tools;

	use \lib\mvc\controllers\login;
	use \lib\mvc\controllers\tools;

	public function __construct($object = false)
	{
		parent::__construct($object);
		// $this->permissions = \lib\utility\option::get('permissions', 'meta');
	}
}
?>