<?php
namespace lib\utility\validate;

// validate iranian national code
// $this->value is passed on function call and we check it
return function()
{
	$mycode   = $this->value;
	$is_valid = false;
	if (mb_strlen($mycode) == 10)
	{
		$main_place = array();
		$i          = 10;
		$p          = 0;
		foreach (str_split($mycode) as $char)
		{
			$main_place[$i] = $char;
			if ($i != 1)
				$p = $p + ($char * $i);

			$i--;
		}
		$ba = fmod($p, 11);
		if ($ba < 2)
		{
			if ($main_place[1] == $ba)
				$is_valid = true;
			else
				$is_valid = false;
		}
		else
		{
			if ($main_place[1] == (11 - $ba))
				$is_valid = true;
			else
				$is_valid = false;
		}
	}
	if ($is_valid)
		return true;


	return false;
}
?>