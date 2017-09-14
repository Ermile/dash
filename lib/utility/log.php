<?php
namespace lib\utility;

/** Log file management **/
class log
{
	/**
	 * Path of the file
	 * @var string
	 */
	private $filepath;

	/**
	 * Max size of a log file
	 * @var int
	 */
	private $max_size = 102400;

	/**
	 * Number of old logs to be preserved
	 * @var int
	 */
	private $nb_old_logs = 3;

	/**
	 * Date format (at the beginning of each added line)
	 * @var string
	 */
	private $date_format = 'Y-m-d H:i';

	/**
	 * Constructor - Defines the log file to be written
	 *
	 * @param string $filepath	Path of the log file
	 */
	public function __construct($filepath){
		$filepath = DATA_DIR.Config::DIR_DATA_LOGS.$filepath;
		if(!file::exists(file::getPath($filepath)))
			throw new Exception('Log directory "'.$filepath.'" not found!');
		$this->filepath = $filepath;
	}

	/**
	 * Writes a line in the log file
	 *
	 * @param string $line
	 */
	public function write($line){
		file::append($this->filepath, date($this->date_format).' - '.$line."\n");
		// If the max size is exceeded
		if(file::getSize($this->filepath) >= $this->max_size){
			file::delete($this->filepath.'.'.$this->nb_old_logs);
			for($i = $this->nb_old_logs; $i >= 1; $i--){
				if(file::exists($this->filepath.($i==1 ? '' : '.'.($i-1))))
					file::rename($this->filepath.($i==1 ? '' : '.'.($i-1)), $this->filepath.'.'.$i);
			}
		}
	}

	/**
	 * Modify the max size of the log file
	 *
	 * @param int $max_size
	 */
	public function setMaxSize($max_size){
		$this->max_size = $max_size;
	}

	/**
	 * Modify the number of old logs to bepreserved
	 *
	 * @param int $nb_old_logs
	 */
	public function setNbOldLogs($nb_old_logs){
		if(!is_int($nb_old_logs) || $nb_old_logs < 1)
			throw new Exception('The number of old logs to be preserved must be at least 1');
		$this->nb_old_logs = $nb_old_logs;
	}

	/**
	 * Modify the date format (at the beginning of each added line)
	 *
	 * @param int $date_format
	 */
	public function setDateFormat($date_format){
		$this->date_format = $date_format;
	}

}
