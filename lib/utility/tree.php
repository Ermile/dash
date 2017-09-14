<?php
namespace lib\utility;


/** Tree Builder Class **/
class tree
{
	/**
	 * Create a tree with recursion function and set it in children of parent
	 * @param  array   $_elements input flat array
	 * @param  integer $_parentId type of default value of parentid
	 * @return [type]             the part of created
	 */
	public static function build(array $_elements, $_tablePrefix, $_parentId = null)
	{
		$branch = array();

		foreach ($_elements as $element)
		{
			if ($element[ $_tablePrefix.'_parent'] == $_parentId)
			{
				$children = self::build($_elements, $_tablePrefix, $element['id']);
				if ($children)
					$element['children'] = $children;

				$branch[] = $element;
			}
		}

		return $branch;
	}


	/**
	 * Create a tree with recursion function
	 * @param  array   $_elements input flat array
	 * @param  integer $_parentId type of default value of parentid
	 * @return [type]             the part of created
	 */
	public static function show(array $_elements, $_tablePrefix, $_parentId = null)
	{
		$branch = array();

		foreach ($_elements as $element)
		{
			if ($element[ $_tablePrefix.'_parent'] == $_parentId)
			{
				$children = self::build($_elements, $_tablePrefix, $element['id']);
				if ($children)
					$element['children'] = $children;

				$branch[] = $element;
			}
		}

		return $branch;
	}
}
