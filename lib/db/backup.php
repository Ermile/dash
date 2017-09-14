<?php
namespace lib\db;

trait backup
{

	/**
	 * create backup from database
	 * @param  [type] $_period [description]
	 * @param  string $_tables [description]
	 * @return [type]          [description]
	 */
	public static function backup($_period = null, $_tables = '*')
	{

		self::connect(true, false);
		mysqli_select_db(self::$link, self::$db_name);

		//get all of the tables
		if($_tables == '*')
		{
			$_tables   = [];
			$result   = mysqli_query(self::$link, 'SHOW TABLES');
			$_tables = self::fetch_all($result, 'Tables_in_'. db_name);
		}
		else
		{
			$_tables = is_array($_tables) ? $_tables : explode(',',$_tables);
		}

		$return = null;

		//cycle through
		foreach($_tables as $table)
		{
			$result     = mysqli_query(self::$link, 'SELECT * FROM '.$table);
			$num_fields = mysqli_num_fields($result);
			$return     .= "DROP TABLE `$table`; ";
			$row2       = mysqli_fetch_row(mysqli_query(self::$link, "SHOW CREATE TABLE `$table` "));
			$return     .= "\n\n".$row2[1].";\n\n";

			for ($i = 0; $i < $num_fields; $i++)
			{
				while($row = mysqli_fetch_row($result))
				{
					$return.= 'INSERT INTO `'.$table.'` VALUES(';
					for($j=0; $j < $num_fields; $j++)
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = str_replace("\n","\\n",$row[$j]);

						if (isset($row[$j]))
						{
							$return.= '"'.$row[$j].'"' ;
						}
						else
						{
							$return.= '""';
						}
						if ($j < ($num_fields-1))
						{
							$return.= ',';
						}
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}

		// if user pass true in period we call clean func
		if($_period === true)
		{
			$clean_result = self::clean(false);
			print_r($clean_result);
			echo "<hr />";
			$_period = null;
		}
		//save file
		$_period    = $_period? $_period.'/':null;
		$dest_dir   = database."backup/$_period";
		$dest_file  = self::$db_name.'_b'. date('Ymd_His').'_'. md5($return) . '.sql';
		// create folder if not exist
		if(!is_dir($dest_dir))
			mkdir($dest_dir, 0755, true);

		// $dest_file = 'db-backup-'.time().'-'.(md5(implode(',',$_tables))).'.sql';
		$handle = fopen($dest_dir. $dest_file, 'w+');
		if(fwrite($handle, $return) === FALSE)
		{
			echo "Cannot write to file ($filename)";
			return false;
		}
		// write successful close file and return true
		fclose($handle);
		echo "Successfully create database backup<br />";
		echo "Location:  $dest_dir<br />";
		echo "File name: $dest_file<hr />";
		return true;
	}


	/**
	 * this function create a backup from db with exec command
	 * the backup file with bz2 compressing method is created in projectdir/backup/db/
	 * for using this function call it with one of below types
	 * db::backup();
	 * db::backup('Daily');
	 * db::backup('Weekly');
	 * @param  [type] $_period the name of subfolder or type of backup
	 * @return [type]          status of running commad
	 */
	public static function backup_dump($_period = null, $_options = [])
	{
		$default_options =
		[
			'lock_tables'   => false,
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);

		$_period    = $_period? $_period.'/':null;
		$db_host    = self::$db_host;
		$db_charset = self::$db_charset;
		$dest_file  = self::$db_name.'.'. date('d-m-Y_H-i-s'). '.sql.bz2';
		$dest_dir   = database."backup/$_period";
		// create folder if not exist
		if(!is_dir($dest_dir))
			mkdir($dest_dir, 0755, true);

		$cmd  = "mysqldump --single-transaction --add-drop-table";
		if(!$_options['lock_tables'])
		{
			$cmd  .= " --skip-lock-tables ";
		}

		$cmd .= " --host='$db_host' --set-charset='$db_charset'";
		$cmd .= " --user='".self::$db_user."'";
		$cmd .= " --password='".self::$db_pass."' '". self::$db_name."'";
		$cmd .= " | bzip2 -c > $dest_dir$dest_file";
		// to import this file
		// bunzip2 < filename.sql.bz2 | mysql -u root -p db_name
		$return_var = NULL;
		$output     = NULL;
		$result     = exec($cmd, $output, $return_var);

		if($return_var === 0)
		{
			\lib\utility\file::download($dest_dir. $dest_file);

			return T_("Back up database complete"). ' '. $dest_file;
		}

		return T_("Error in backup database");
	}


	/**
	 * this function delete older backup file from db backup folder
	 * you can pass type of clean (folder) and days to keep
	 * call function with below syntax
	 * db::clean();
	 * db::clean('Daily');
	 * db::clean('Weekly', 3);
	 * @param  [type] $_period the name of subfolder or type of backup
	 * @param  [type] $_arg    value of the days for keep files
	 * @return [type]          the result of cleaning seperate by type in array
	 */
	public static function clean($_period = null, $_arg = null)
	{
		$days_to_keep = $_arg[0]? $_arg[0]: 3;
		if($_period === false)
		{
			$days_to_keep = 100;
		}
		$_period      = $_period? $_period.'/':null;
		$dest_dir     = database."backup/$_period";
		$result       =
		[
			'folders'   => 0,
			'files'     => 0,
			'deleted'   => 0,
			'duplicate' => 0,
			'skipped'   => 0,
		];

		if(!is_dir($dest_dir))
			return false;

		$handle              = opendir($dest_dir);
		$keep_threshold_time = strtotime("-$days_to_keep days");
		$files_list          = [];
		while (false !== ($file = readdir($handle)))
		{
			if($file === '.' || $file === '..')
			 continue;

			$dest_file_path = "$dest_dir/$file";
			if(!is_dir($dest_file_path))
			{
				$result['files'] += 1;
				$file_time       = filemtime($dest_file_path);
				$file_code = substr($file, strrpos($file, '_')+1, -4);
				if(isset($files_list[$file_code]))
				{
					$result['duplicate'] += 1;
					unlink($dest_file_path);
				}
				else
				{
					$files_list[$file_code] = $file;
				}
				if($file_time < $keep_threshold_time)
				{
					$result['deleted'] += 1;
					unlink($dest_file_path);
				}
				else
				{
					$result['skipped'] += 1;
				}
			}
			else
			{
				$result['folders'] += 1;
			}
		}
		$result['list'] = $files_list;
		return $result;
	}
}
?>