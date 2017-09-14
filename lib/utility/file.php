<?php
/** In The name of Allah **/
/*  Proudly made in IRAN, powered by Dash, under licence of Ermile */

/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// |                                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2014-2016 The Ermile Company                           |
// | 010001010111001001101101011010010110110001100101                     |
// +----------------------------------------------------------------------+
// | Authors: Original Author <author@example.com>                        |
// |          Javad Evazzadeh <J.Evazzadeh@live.com>                      |
// +----------------------------------------------------------------------+
// $Id:$
/**
 * Class and Function List:
 * Function list:
 * - read()
 * - write()
 * - append()
 * - makeDir()
 * - rename()
 * - move()
 * - copy()
 * - exists()
 * - delete()
 * - upload()
 * - getExtension()
 * - getName()
 * - getPath()
 * - getSize()
 * - humanReadableSize()
 * Classes list:
 * - File
 */
namespace lib\utility;
use \lib\utility\upload;

/** Files management : write, read, delete, upload... **/
class file
{

	/**
	 * Reads a file and returns its content
	 *
	 * @param string $filepath	Path of the file
	 * @return string|bool	Content of the file, or false on failure
	 */
	public static function read( $filepath )
	{
		return file_get_contents( $filepath );
	}


	/**
	 * Writes in a file
	 *
	 * @param string $filepath	Path of the file
	 * @param string $content	Content of the file
	 * @return int|bool	Number of bytes that were written to the file, or false on failure.
	 */
	public static function write( $filepath, $content )
	{
		return file_put_contents( $filepath, $content, LOCK_EX );
	}


	/**
	 * Writes at the end of a file
	 *
	 * @param string $filepath	Path of the file
	 * @param string $content	Content of the file
	 * @return int|bool	Number of bytes that were written to the file, or false on failure.
	 */
	public static function append( $filepath, $content )
	{
		return file_put_contents( $filepath, $content, FILE_APPEND | LOCK_EX );
	}


	/**
	 * Creates a new directory
	 *
	 * @param string $_dirpath	Path of the new dir
	 * @param int $_mode			Change the mode of the dir
	 * @param bool $_recursive	Creates the dir recursively
	 * @return bool	True on success, false on failure
	 */
	public static function makeDir($_dirpath, $_mode = 0775, $_recursive = false )
	{
		if(!$_mode)
		{
			$_mode = 0775;
		}
		if(!file_exists($_dirpath))
		{
			return @mkdir( $_dirpath, $_mode, $_recursive );
		}
		return null;
	}


	/**
	 * Renames a file or a directory
	 *
	 * @param string $oldname	Old name of the file / dir
	 * @param string $newname	New name of the file / dir
	 * @return bool	True on success, false on failure
	 */
	public static function rename($oldname, $newname )
	{
		if(!is_dir(dirname($newname)))
		{
			self::makeDir(dirname($newname), null, true);
		}
		if(self::exists($oldname))
		{
			return rename($oldname, $newname );
		}
		else
		{
			return false;
		}
	}


	/**
	 * get the file content type
	 *
	 * @param      <type>  $_file_path  The file path
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function content_type($_file_path)
	{
		return mime_content_type($_file_path);
	}


	/**
	 * Moves a file or a directory
	 *
	 * @param string $path		Name of the file / dir
	 * @param string $newdir	Name of the new parent dir
	 * @return bool	True on success, false on failure
	 */
	public static function move( $path, $newdir, $_raw_rename = false )
	{
		if($_raw_rename)
		{
			return self::rename( $path, $newdir );
		}
		else
		{
			$newdir = rtrim( $newdir, '/' );
			return self::rename( $path, $newdir . '/' . basename( $path ) , $_raw_rename);
		}
	}


	/**
	 * Copies a file or a directory
	 *
	 * @param string $name		Name of the file / dir
	 * @param string $copyname	Name of the copy of the file / dir
	 * @return bool	True on success, false on failure
	 */
	public static function copy( $name, $copyname )
	{

		// If it's a dir...
		if( is_dir( $name ) )
		{
			if( !self::makeDir( $copyname ) )
			{
				return false;
			}
			$handle = opendir( $name );

			while( $filename = readdir( $handle ) )
			{
				if( $filename != '.' && $filename != '..' )
				{
					if( !self::copy( $name . '/' . $filename, $copyname . '/' . $filename ) )
					{
						self::delete( $copyname );
						return false;
					}
				}
			}
			closedir( $handle );
			return true;
			// If it's a file
		}
		else if( file_exists( $name ) )
		{
			return copy( $name, $copyname );
		}
		return false;
	}


	/**
	 * Checks whether a file or directory exists
	 *
	 * @param string $path		Name of the file / dir
	 * @return bool	True if the file or directory exists; False otherwise
	 */
	public static function exists( $path )
	{
		return file_exists( $path );
	}


	public static function existsComplete( $_path )
	{
		// check for simple files
		if(file_exists($_path))
		{
			return true;
		}
		// else if the file is url and exist
		elseif(!filter_var($_path, FILTER_VALIDATE_URL) === false)
		{
			// read header of file and if exist return true
			$headers = @get_headers($_path);
			if($headers && stripos($headers[0], "200 OK"))
			{
				return true;
			}
		}

		// default return value if file is not exist
		return false;
	}


	/**
	 * [alternative description]
	 * @param  [type] $_file        [description]
	 * @param  [type] $_alternative [description]
	 * @return [type]               [description]
	 */
	public static function alternative($_file, $_alternative = null, $_default = null)
	{
		if(self::existsComplete($_file))
		{
			return $_file;
		}
		elseif($_alternative && self::existsComplete($_alternative))
		{
			return $_alternative;
		}

		return $_default;
	}


	/**
	 * Deletes file or a directory recursively
	 *
	 * @param string $path	Name of the file / dir
	 * @return bool	True on success, false on failure
	 */
	public static function delete( $path )
	{

		// If it's a dir...
		if( is_dir( $path ) )
		{
			$handle = opendir( $path );

			while( $filename = readdir( $handle ) )
			{
				if( $filename != '.' && $filename != '..' )
				{
					if( !self::delete( $path . '/' . $filename ) )return false;
				}
			}
			closedir( $handle );

			return rmdir( $path );

			// If it's a file
		}
		else if( file_exists( $path ) )
		{
			return unlink( $path );
		}
		return true;
	}


	/**
	 * Recovers an uploaded file and store it in the tmp dir
	 *
	 * @param string $name	Name of the input[type=file] tag
	 * @return string|array|bool	Path of the file in the tmp dir, or array of paths if multi-upload, false on failure
	 */
	public static function upload( $_name , $_path = null)
	{
		if( upload::_FILES($_name) && isset( upload::_FILES($_name)['name'] ) && upload::_FILES($_name)['size'] != 0 )
		{
			// Multi-upload
			if( is_array( upload::_FILES($_name)['name'] ) )
			{
				$paths = array();

				for( $i = 0; $i < count( upload::_FILES($_name)['name'] ); $i++ )
				{
					if(!$_path)
					{
						$path = DATA_DIR . Config::DIR_DATA_TMP . upload::_FILES($_name)['name'][ $i ];
					}
					else
					{
						$path = $_path;
					}

					if( move_uploaded_file( upload::_FILES($_name)['tmp_name'][ $i ], $path ) )
					{
						@chmod( $path, 0777 );
						$paths[] = $path;
					}
				}

				if( count( $paths ) != 0 )
				{
					return $paths;
				}

				// Single file
			}
			else
			{
				if(!$_path)
				{
					$path = DATA_DIR . Config::DIR_DATA_TMP . upload::_FILES($_name)['name'];
				}
				else
				{
					$path = $_path;
				}

				if( move_uploaded_file( upload::_FILES($_name)['tmp_name'], $path ) )
				{
					@chmod( $path, 0777 );
					return $path;
				}
			}
		}
		return false;
	}


	/**
	 * Returns the extension of a file
	 *
	 * @param string $filename	Name of the file
	 * @return string	Extension of the file
	 */
	public static function getExtension( $filename )
	{
		$pos = strrpos( $filename, '.' );
		return $pos === false ? '' : substr( $filename, $pos + 1 );
	}


	/**
	 * Returns the name of a file
	 *
	 * @param string $filepath	Path of the file
	 * @return string	Name of the file
	 */
	public static function getName( $filepath )
	{
		return basename( $filepath );
	}


	/**
	 * Returns the directory of a file
	 *
	 * @param string $filepath	Path of the file
	 * @return string	Directory of the file
	 */
	public static function getPath( $filepath )
	{
		return dirname( $filepath );
	}


	/**
	 * Returns the size of a file
	 *
	 * @param string $filepath	Path of the file
	 * @return int	Size of the file
	 */
	public static function getSize( $filepath )
	{
		return filesize( $filepath );
	}


	/**
	 * Returns a size readable by humans
	 *
	 * @param int $size	Size in bytes
	 * @return string	Human readable size
	 */
	public static function humanReadableSize( $_size, $accuracy = 0 )
	{
		if( $_size > 1024 * 1024 * 1024 )
		{
			return round( $_size /( 1024 * 1024 * 1024 ), $accuracy ) . ' Go';
		}

		if( $_size > 1024 * 1024 )
		{
			return round( $_size /( 1024 * 1024 ), $accuracy ) . ' Mo';
		}

		if( $_size > 1024 )
		{
			return round( $_size / 1024, $accuracy ) . ' Ko';
		}

		return $_size . ' octets';
	}


	/**
	 * force browser to download file
	 * @param  [type] $_filepath [description]
	 * @return [type]           [description]
	 */
	public static function download($_filePath, $_fileName = null, $_fileMime = null)
	{
		// Quick check to verify that the file exists
		if(!file_exists($_filePath))
		{
			// return false;
		}

		if(!$_fileName)
		{
			$_fileName = basename($_filePath);
		}

		if(!$_fileMime)
		{
			// get file mime from upload library
			$_fileMime = upload::extCheck($_filePath);
			$_fileMime = $_fileMime['mime'];
		}

			// Force the download
			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Last-Modified: '. gmdate ('D, d M Y H:i:s', filemtime ($_filePath)). ' GMT');
			header("Content-disposition: attachment; filename=\"" .basename($_fileName) ."\"");
			header('Content-Length: ' .filesize($_filePath));
			header('Content-Transfer-Encoding: binary');

		// Generate the server headers to force the download process
		if( strstr( $_SERVER['HTTP_USER_AGENT'], "MSIE" ) )
		{
			header("Content-Type: " .$_fileMime .";");
		}
		else
		{
			header( "Content-Type: ".$_fileMime, true, 200 );
			header('Cache-Control: private',false);
			header('Accept-Ranges: bytes');
			header('Connection: close');
		}

		readfile($_filePath);
		exit();
	}


	/**
	 * open file as fopen function
	 *
	 * @param      <type>  $_path  The path
	 */
	public static function open($_path, $_options = [])
	{
		$max_size = \lib\utility\upload::max_file_upload_in_bytes(true);
		$default_options =
		[
			'read_size' => 1024 * 5, // 5 MB
			'tmp_path'  => '/tmp',
			'max_size'  => $max_size,
		];

		$_options = array_merge($default_options, $_options);
		$new_path = tempnam($_options['tmp_path'], 'ERMILE_');

		$file     = @fopen($_path, 'r');

		if(!$file)
		{
			\lib\debug::error(T_('File not exist'));
			// throw new \RuntimeException(T_('File not exist'));
		}

		$tmp_file = fopen($new_path, 'w');

		$uploaded_size = 0;

		$break = false;
		while(!feof($file))
		{
			fwrite($tmp_file, fread($file, $_options['read_size']));
			$uploaded_size += $_options['read_size'];
			if($uploaded_size > $_options['max_size'])
			{
				$break = true;
			}

			if($break)
			{
				break;
			}
		}

		if($break)
		{
			fclose($file);
			fclose($tmp_file);
			\lib\debug::error(T_('Exceeded filesize limit'));
			// throw new \RuntimeException(T_('Exceeded filesize limit'));
			return false;
		}

		fclose($file);
		fclose($tmp_file);
		return $new_path;
	}



	/**
	 * set permission to file
	 *
	 * @param      <type>   $_url   The url
	 * @param      integer  $_perm  The permission
	 */
	public function perm($_path, $_perm = 0644)
	{
		return @chmod($_path, $_perm);
	}
}
?>