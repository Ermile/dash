<?php
class iautoload extends autoload
{
	static function get_file_name($split_name){
		list($prefix, $sub_path, $exec_file) = self::ifile_splice($split_name);
		$prefix_file = null;
		$file_addr = parent::get_file_name($split_name);
		if(!preg_grep("/^$prefix$/", self::$core_prefix)){
			if(!$file_addr){
				$prefix_file = \lib\router::get_repository();
				$prefix_file = preg_replace("#\/[^\/]+\/?$#", '', $prefix_file);
				if(file_exists(addons. $prefix. '/' .$sub_path. $exec_file))
				{
					$file_addr = addons. $prefix. '/' .$sub_path. $exec_file;
				}
			}
		}
		return $file_addr;
	}
}
?>


