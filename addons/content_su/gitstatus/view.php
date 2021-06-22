<?php
namespace content_su\gitstatus;

class view
{
	public static function config()
	{
		\dash\data::page_title(T_("Git Status"));
		\dash\data::page_desc(T_('Check file status on this project'));

		echo "<h1><a href='". \dash\url::this(). "?discard=all'>Discard all</a></h1>";

		if(\dash\request::get('discard') === 'all')
		{
			self::gitDiscard();
			\dash\redirect::to(\dash\url::this());
		}

		$result = self::gitStatus();

		if(is_array($result))
		{
			foreach ($result as $key => $value)
			{
				echo $value;
			}
		}

		echo "<hr>";

		// $result = self::gitDiff();

		// if(is_array($result))
		// {
		// 	foreach ($result as $key => $value)
		// 	{
		// 		echo $value;
		// 	}
		// }

		\dash\code::boom();
	}

		public static function gitDiscard()
	{
		$location = null;
		$result   = [];

		// pull dash
		if(is_dir(root. 'dash'))
		{
			$location = '../dash';
		}
		elseif(is_dir(root. '../dash'))
		{
			$location = '../../dash';
		}

		$result[] =  \dash\utility\git::gitdiscard($location);

		// pull current project
		$name = \dash\url::root();
		$location = '../../'. $name;
		$result[] =  \dash\utility\git::gitdiscard($location);

	}


	public static function gitStatus()
	{
		$location = null;
		$result   = [];

		// pull dash
		if(is_dir(root. 'dash'))
		{
			$location = '../dash';
		}
		elseif(is_dir(root. '../dash'))
		{
			$location = '../../dash';
		}

		$result[] = "<h1>GIT STATUS</h1>";
		$result[] = "<h2>Dash</h2>";
		$result[] =  \dash\utility\git::gitstatus($location);

		// pull current project
		$name = \dash\url::root();

		$result[] = "<hr>";
		$result[] = "<h2>$name <small>Current Project</small></h2>";
		$result[] =  \dash\utility\git::gitstatus(root);

		return $result;
	}

	public static function gitDiff()
	{
		$location = null;
		$result   = [];

		// pull dash
		if(is_dir(root. 'dash'))
		{
			$location = '../dash';
		}
		elseif(is_dir(root. '../dash'))
		{
			$location = '../../dash';
		}

		$result[] = "<h1>GIT DIFF</h1>";
		$result[] = "<h2>Dash</h2>";
		$result[] =  \dash\utility\git::gitdiff($location);

		// pull current project
		$name = \dash\url::root();
		$location = '../../'. $name;

		$result[] = "<hr>";
		$result[] = "<h2>$name <small>Current Project</small></h2>";
		$result[] =  \dash\utility\git::gitdiff(root);

		return $result;
	}


}
?>