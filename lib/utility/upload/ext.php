<?php
namespace lib\utility\upload;

trait ext
{

	/**
	 * Get the MIME and type of file extension.
	 * @param string $_ext File extension
	 * @access public
	 * @return string MIME type of file.
	 * @static
	 */
	public static function extCheck($_ext = '')
	{
		// if pass filepath
        if(file_exists($_ext))
        {
        	$fileInfo = pathinfo($_ext);
        	$_ext     = mb_strtolower($fileInfo['extension']);
        }

		$mimes =
		[
			// archive
			'gtar'     => [ 'type' => 'archive',    'mime' => 'application/x-gtar'],
			'tar'      => [ 'type' => 'archive',    'mime' => 'application/x-tar'],
			'tgz'      => [ 'type' => 'archive',    'mime' => 'application/x-tar'],
			'zip'      => [ 'type' => 'archive',    'mime' => 'application/zip'],
			'7z'       => [ 'type' => 'archive',    'mime' => 'application/x-7z-compressed'],
			'rar'      => [ 'type' => 'archive',    'mime' => 'application/x-rar-compressed'],
			// audio
			'mp3'      => [ 'type' => 'audio',      'mime' => 'audio/mpeg'],
			'wav'      => [ 'type' => 'audio',      'mime' => 'audio/x-wav'],
			'ogg'      => [ 'type' => 'audio',      'mime' => 'audio/ogg'],
			'wma'      => [ 'type' => 'audio',      'mime' => 'audio/x-ms-wma'],

			// image
			'bmp'      => [ 'type' => 'image',      'mime' => 'image/bmp'],
			'gif'      => [ 'type' => 'image',      'mime' => 'image/gif'],
			'jpeg'     => [ 'type' => 'image',      'mime' => 'image/jpeg'],
			'jpg'      => [ 'type' => 'image',      'mime' => 'image/jpeg'],
			'png'      => [ 'type' => 'image',      'mime' => 'image/png'],
			'tif'      => [ 'type' => 'image',      'mime' => 'image/tiff'],
			'svg'      => [ 'type' => 'image',      'mime' => 'image/svg+xml'],
			// pdf
			'pdf'      => [ 'type' => 'pdf',        'mime' => 'application/pdf'],
			// video
			'mpeg'     => [ 'type' => 'video',      'mime' => 'video/mpeg'],
			'mpg'      => [ 'type' => 'video',      'mime' => 'video/mpeg'],
			'mp4'      => [ 'type' => 'video',      'mime' => 'video/mp4'],
			'mov'      => [ 'type' => 'video',      'mime' => 'video/quicktime'],
			'avi'      => [ 'type' => 'video',      'mime' => 'video/x-msvideo'],
			'dvi'      => [ 'type' => 'video',      'mime' => 'application/x-dvi'],
			// word
			'doc'      => [ 'type' => 'word',       'mime' => 'application/msword'],
			'docx'     => [ 'type' => 'word',       'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
			// excel
			'xls'      => [ 'type' => 'excel',      'mime' => 'application/vnd.ms-excel'],
			'xlsx'     => [ 'type' => 'excel',      'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
			// powerpoint
			'ppt'      => [ 'type' => 'powerpoint', 'mime' => 'application/vnd.ms-powerpoint'],
			'pptx'     => [ 'type' => 'powerpoint', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
			'ppsx'     => [ 'type' => 'powerpoint', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow'],
			// code
			'js'       => [ 'type' => 'code',       'mime' => 'application/x-javascript'],
			'dll'      => [ 'type' => 'code',       'mime' => 'application/octet-stream'],
			// diallow file list
			'php'      => [ 'type' => 'code',       'mime' => 'application/x-httpd-php'],
			'php5'     => [ 'type' => 'code',       'mime' => 'application/x-httpd-php'],
			'exe'      => [ 'type' => 'code',       'mime' => 'application/octet-stream'],
			'bat'      => [ 'type' => 'code',       'mime' => 'application/x-bat'],
			'bin'      => [ 'type' => 'code',       'mime' => 'application/macbinary'],
			'htaccess' => [ 'type' => 'code',       'mime' => 'application/x-jar'],
			// text
			'rtx'      => [ 'type' => 'text',       'mime' => 'text/richtext'],
			'rtf'      => [ 'type' => 'text',       'mime' => 'text/rtf'],
			'log'      => [ 'type' => 'text',       'mime' => 'text/plain'],
			'text'     => [ 'type' => 'text',       'mime' => 'text/plain'],
			'txt'      => [ 'type' => 'text',       'mime' => 'text/plain'],
			'xml'      => [ 'type' => 'text',       'mime' => 'text/xml'],
			'xsl'      => [ 'type' => 'text',       'mime' => 'text/xml'],
			'css'      => [ 'type' => 'text',       'mime' => 'text/css'],
			'htm'      => [ 'type' => 'text',       'mime' => 'text/html'],
			'html'     => [ 'type' => 'text',       'mime' => 'text/html'],
			'shtml'    => [ 'type' => 'text',       'mime' => 'text/html'],
			'xht'      => [ 'type' => 'text',       'mime' => 'application/xhtml+xml'],
			'xhtml'    => [ 'type' => 'text',       'mime' => 'application/xhtml+xml'],
			// file
			'psd'      => [ 'type' => 'file',       'mime' => 'application/octet-stream'],
			'eps'      => [ 'type' => 'file',       'mime' => 'application/postscript'],
			'apk'      => [ 'type' => 'file',       'mime' => 'application/vnd.android.package-archive'],
			'chm'      => [ 'type' => 'file',       'mime' => 'application/vnd.ms-htmlhelp'],
			'jar'      => [ 'type' => 'file',       'mime' => 'application/x-jar'],
		];

		// if exist in list return it
		if(array_key_exists(mb_strtolower($_ext), $mimes))
		{
			$myResult = $mimes[mb_strtolower($_ext)];
		}
		else
		{
			$myResult = ['type' => 'file', 'mime' => 'application/octet-stream'];
		}

		$myResult['disallow'] = null;

		if(in_array($_ext, self::$extentionsDisallow))
		{
			$myResult['disallow'] = true;
		}
		// else return the
		return $myResult;
	}
}
?>