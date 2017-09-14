<?php
namespace lib\utility;

/** Get and show server info **/
class tools
{
	/**
	 * useful tools for doing some job
	 * v1.0
	 */

	/**
	 * run linfo library
	 * @return [type] [description]
	 */
	public static function linfo()
	{
		require addons.'lib/linfo/index.php';
	}

	public static function glob_recursive($directory, &$directories = [])
	{
		$dir_list = glob($directory, GLOB_ONLYDIR | GLOB_NOSORT);
		if($dir_list)
		{
			foreach($dir_list as $folder)
			{
				$directories[] = $folder;
				self::glob_recursive("{$folder}/*", $directories);
			}
		}
	}

	/**
	 * find files in directory recursively
	 * @param  [type] $directory  [description]
	 * @param  array  $extensions [description]
	 * @return [type]             [description]
	 */
	public static function findFiles($directory, $extensions = [])
	{
		self::glob_recursive($directory, $directories);
		$files = [];
		foreach($directories as $directory)
		{
			foreach($extensions as $extension)
			{
				foreach(glob("{$directory}/*.{$extension}") as $file)
				{
					$files[$extension][] = $file;
				}
			}
		}
		return $files;
	}


	public static function mergefiles($_dest, $_path = null, $_ext= ['php'], $_except = [])
	{
		if(!$_dest)
		{
			return null;
		}
		if($_path === null)
		{
			$_path = root;
		}
		if($_except === true)
		{
			$_except = ['translation.php', 'trans_twig.php', 'trans_addons.php'];
		}
		// create real path
		$_path = realpath($_path);
		// save list of files
		$files = self::findFiles($_path,$_ext);
		// open dest file to save data in it
		file_put_contents($_dest, "");
		$out   = fopen($_dest, "w");
		// for each extention
		foreach ($files as $extension => $fileList)
		{
			foreach ($fileList as $key => $filePath)
			{
				if(!in_array(basename($filePath), $_except))
				{
					file_put_contents($_dest, realpath($filePath). "\r\n", FILE_APPEND);
					file_put_contents($_dest, file_get_contents($filePath, true). "\r\n\n\n", FILE_APPEND);
				}
			}
		}

		return "ok: $_dest\n";
	}
}
?>