<?php
namespace lib\telegram;

/** telegram keyboard creator library**/
class keyboard extends tg
{
	/**
	 * this library create best keyboard for input list
	 * v1.2
	 */


	/**
	 * return answer keyborad array or keyboard
	 * @param  boolean $_onlyArray [description]
	 * @return [type]              [description]
	 */
	public static function draw($_list = null, $_onlyArray = null, $_type = 'keyboard')
	{
		if(!$_list)
		{
			return null;
		}
		if($_onlyArray === true)
		{
			// return array contain only list
			$_list = array_keys($_list);
			return $_list;
		}

		$menu =
		[
			$_type              => [],
		];

		if($_type === 'keyboard')
		{
			$menu['one_time_keyboard'] = false;
			$menu['resize_keyboard']   = true;
			$menu['selective']         = true;

			if($_onlyArray === 'public')
			{
				$menu['selective'] = false;
			}
			if($_onlyArray === 'fixed')
			{
				$menu['resize_keyboard'] = false;
			}
			if($_onlyArray === 'one_time')
			{
				$menu['one_time_keyboard'] = true;
			}
		}

		// calculate number of item in each row
		// max row can used is 3
		$inEachRow  = 1;
		$itemsCount = count($_list);
		$rowUsed    = $itemsCount;
		$rowMax     = 4;
		// if count of items is divided by 2
		if(($itemsCount % 2) === 0)
		{
			$inEachRow = 2;
			$rowUsed   = $itemsCount / 2;
			if($rowUsed > $rowMax)
			{
				if(($itemsCount % 3) === 0)
				{
					$inEachRow = 3;
					$rowUsed   = $itemsCount / 3;
				}
			}
		}
		// if count of items is divided by 3
		if($itemsCount > 6 && ($itemsCount % 3) === 0)
		{
			$inEachRow = 3;
			$rowUsed   = $itemsCount / 3;
		}

		$i = 0;
		foreach ($_list as $key => $value)
		{
			// calc row number
			$row = floor($i/ $inEachRow);
			// add to specefic row
			$menu[$_type][$row][] = $value;
			// increment counter
			$i++;
		}
		return $menu;
	}
}
?>