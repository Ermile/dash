<?php
namespace lib\utility\validate;

// validate birthyear
// $this->value is passed on function call and we check it
return function()
{
	$year = $this->value;

	if(mb_strlen($year) === 4)
	{
		if(substr($year, 0, 2) == '19' || substr($year, 0, 2) == '20')
			return true;

		if(substr($year, 0, 2) == '13' || substr($year, 0, 2) == '14')
		{
			$this->value = $year + 622;
			return true;
		}
		else
			return false;
	}
	elseif(mb_strlen($year) === 2)
	{
		$this->value = (1300 + $year) + 622;
		return true;
	}


	return false;
}
?>