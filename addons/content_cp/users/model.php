<?php
namespace addons\content_cp\users;

use \lib\utility;
use \lib\debug;
class model extends \mvc\model
{
	public function users_list($_args, $_fields = [])
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

		$result = \lib\db\users::search($search, $meta);
		return $result;
	}
}
?>
