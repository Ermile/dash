<?php
namespace lib\utility;

/** zip files management **/
class zip
{
	public static function create($_zipAddr, $_file, $_fileNewName = null)
	{
		$zip = new \ZipArchive();

		if ($zip->open($_zipAddr, \ZIPARCHIVE::OVERWRITE) !== TRUE)
		{
			// if file not exist, add to existing file
			if ($zip->open($_zipAddr, \ZipArchive::CREATE) !== TRUE)
			{
				return("cannot open <$_zipAddr>\n");
			}
		}

		// add file to zip archive
		$zip->addFile($_file, $_fileNewName);
		$zip->close();

		return true;
	}


	/**
	 * [download_on_fly description]
	 * @param  [type] $_addr [description]
	 * @param  [type] $_name [description]
	 * @return [type]        [description]
	 */
	public static function download_on_fly($_addr, $_name = null)
	{
		if($_name)
		{
			$_name .= '.zip';
		}
		else
		{
			$_name = 'test.zip';
		}
		\lib\utility\file::download($_addr, $_name, 'archive/zip');
		// exit to download it
		exit();
	}

}
?>