<?php
namespace addons\content_api\v1\home\tools;

trait _use
{
	use get_token;
	use options;

	use \addons\content_api\v1\file\tools\link;
	use \addons\content_api\v1\file\tools\get;

	use \addons\content_api\v1\user\tools\add;
	use \addons\content_api\v1\user\tools\get;

	use \addons\content_api\v1\parent\tools\add;
	use \addons\content_api\v1\parent\tools\get;
	use \addons\content_api\v1\parent\tools\delete;

	use \addons\content_api\v1\comment\tools\add;
	use \addons\content_api\v1\comment\tools\get;

}
?>