<?php
namespace addons\content_cp\invoicedetails;

use \lib\utility;
use \lib\debug;
class model extends \mvc\model
{
	public function invoicedetails_list($_args, $_fields = [])
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

		$result = \lib\db\invoice_details::search($search, $meta);

		return $result;
	}
}
?>
