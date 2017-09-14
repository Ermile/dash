<?php
class cronjob
{
	/**
	 * file paths finded
	 *
	 * @var        array
	 */
	public $paths = [];

	/**
	 * Searches for the first match.
	 * sarch in all project and finde 'cronjob.php'
	 */
	public function find()
	{
		$this_dir = __DIR__;
		chdir($this_dir);
		chdir("../../../..");

		$path = realpath(''). DIRECTORY_SEPARATOR;
		echo $path;

		$directory   = new \RecursiveDirectoryIterator($path);
		$flattened   = new \RecursiveIteratorIterator($directory);
		$flattened->setMaxDepth(1);
		$files       = new \RegexIterator($flattened, "/cronjob\\.php$/");

		foreach($files as $file)
		{

			$file_name = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
			// var_dump($file_name);
			if($file_name === (__DIR__ . DIRECTORY_SEPARATOR . 'cronjob.php'))
			{
				// the file fined is this file!
				// never run this file when in this file!
			}
			else
			{
				$this->paths[] = $file_name;
			}
		}
		var_dump($this->paths);

	}

	/**
	 * exec all finded cronjob.php
	 */
	public function run()
	{
		$this->find();

		if(!empty($this->paths))
		{
			foreach ($this->paths as $key => $value)
			{
				if($value && is_string($value) && file_exists($value))
				{
					exec("php $value");
				}
			}
		}
	}
}

(new cronjob)->run();

?>