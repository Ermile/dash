<?php
namespace lib\validator;

class pack
{
	public function check($value, $validator, $group = 'public')
	{
		return new \lib\validator($value, $validator, $group);
	}

	public function make()
	{
		return new maker();
	}
}
?>