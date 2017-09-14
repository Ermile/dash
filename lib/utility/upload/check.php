<?php
namespace lib\utility\upload;

trait check
{

	/**
	 * Check for invalid upload process
	 * @param  string self::$fieldName [description]
	 * @return [type]        [description]
	 */
	public static function invalid($_name = 'upfile', $_maxSize = null)
	{
		self::$fieldName = $_name;

		// Undefined | Multiple Files | $_FILES Corruption Attack
		// If this request falls under any of them, treat it invalid.
		if ( !isset(self::_FILES(self::$fieldName)['error']) || is_array(self::_FILES(self::$fieldName)['error']))
		{
			\lib\debug::error(T_('Invalid parameters'), null, 'upload');
			return true; // yes, invalid file
			// throw new \RuntimeException(T_('Invalid parameters'));
		}

		// Check self::_FILES(self::$fieldName)['error'] value.
		switch (self::_FILES(self::$fieldName)['error'])
		{
			case UPLOAD_ERR_OK:
				break;

			case UPLOAD_ERR_NO_FILE:
				\lib\debug::error(T_('No file sent'), null, 'upload');
				return true; // yes, invalid file
				// throw new \RuntimeException(T_('No file sent'));

			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				\lib\debug::error(T_('Exceeded filesize limit'), null, 'upload');
				return true; // yes, invalid file
				// throw new \RuntimeException(T_('Exceeded filesize limit'));

			default:
				\lib\debug::error(T_('Unknown errors'), null, 'upload');
				return true; // yes, invalid file
				// throw new \RuntimeException(T_('Unknown errors'));
		}

		$fileInfo           = pathinfo(self::_FILES(self::$fieldName)['name']);
		if(isset($fileInfo['filename']))
		{
			self::$fileName     = $fileInfo['filename'];
		}
		if(isset($fileInfo['extension']))
		{
			self::$fileExt      = mb_strtolower($fileInfo['extension']);
		}

		$extCheck           = self::extCheck(self::$fileExt);

		self::$fileType     = isset($extCheck['type']) ? $extCheck['type'] : null;
		self::$fileMime     = isset($extCheck['mime']) ? $extCheck['mime'] : null;
		self::$fileDisallow = isset($extCheck['disallow']) ? $extCheck['disallow'] : null;

		if(!$_maxSize)
		{
			$_maxSize = self::max_file_upload_in_bytes(true);
		}

		// Check filesize here.
		self::$fileSize = self::_FILES(self::$fieldName)['size'];
		if ( self::$fileSize > $_maxSize)
		{
			\lib\debug::error(T_('Exceeded filesize limit'), null, 'upload');
			return true; // yes, invalid file
			// throw new \RuntimeException(T_('Exceeded filesize limit'));
		}

		//check file extention with allowed extention list
		// set file data like name, ext, mime
		// file with long name does not allowed in our system
		if(mb_strlen(self::$fileName) > 200 || strpos(self::$fileName, 'htaccess') !== false)
		// if(mb_strlen(self::$fileName) > 200)
		{
			\lib\debug::error(T_('Exceeded file name limit'), null, 'upload');
			return true; // yes, invalid file
			// throw new \RuntimeException(T_('Exceeded file name limit'));
		}
		// file with long extension does not allowed in our system
		if(mb_strlen(self::$fileExt) > 10 || self::$fileDisallow )
		{
			\lib\debug::error(T_('Exceeded file extension limit'), null, 'upload');
			return true; // yes, invalid file
			// throw new \RuntimeException(T_('Exceeded file extension limit'));
		}

		self::$fileFullName = \lib\utility\filter::slug(self::$fileName). '.'. self::$fileExt;
		self::$fileMd5      = md5_file(self::_FILES(self::$fieldName)['tmp_name']);

		if(is_array(self::$extentions) && !in_array(self::$fileExt, self::$extentions))
		{
			\lib\debug::error(T_("We don't support this type of file"), null, 'upload');
			return true; // yes, invalid file
			// throw new \RuntimeException(T_("We don't support this type of file"));
		}

		// DO NOT TRUST self::_FILES(self::$fieldName)['mime'] VALUE !!
		// Check MIME Type by yourself.
		// Alternative check
		if(function_exists('finfo'))
		{
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			// var_dump($finfo);
			// if (false === $ext = array_search( $finfo->file(self::_FILES(self::$fieldName)['tmp_name']), self::$extentions ), true ))
			// {
			// \lib\debug::error(T_('Invalid file format.'), null, 'upload');
			// return true; // yes, invalid file
			// throw new \RuntimeException(T_('Invalid file format.'));
			// }
			self::$fileMime = mime_content_type($fileInfo['basename']);
		}

		// it is not invalid, that's mean it's a valid upload
		return false;
		// }
		// catch (\RuntimeException $e)
		// {
		// 	return $e->getMessage();
		// }
	}
}
?>