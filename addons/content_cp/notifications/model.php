<?php
namespace addons\content_cp\notifications;

use \lib\utility;
use \lib\debug;
class model extends \mvc\model
{
	public function notifications_list($_args, $_fields = [])
	{
		$meta   = [];
		$meta['admin'] = true;

		$search = null;
		if(utility::get('search'))
		{
			$search = utility::get('search');
		}

		foreach ($_fields as $key => $value)
		{
			if(isset($_args->get($value)[0]))
			{
				$meta[$value] = $_args->get($value)[0];
			}
		}

		$result = \lib\db\notifications::search($search, $meta);
		// var_dump($result);exit();
		return $result;
	}
}
?>
