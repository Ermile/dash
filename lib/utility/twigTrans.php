<?php
namespace lib\utility;

/** twig Trans Extractor class **/
class twigTrans
{
	// Create a files in language folder has contain twig trans value
	public static function extract($_path = null, $_update = null)
	{
		ob_start();

		echo "<!DOCTYPE html><meta charset='UTF-8'/><title>Extract text form twig files</title><body style='padding:0 1%;margin:0 1%;direction:ltr;'>";

		$export_file_name = 'twig';
		switch ($_path)
		{
			case 'addons':
				$mypath = realpath(addons).DIRECTORY_SEPARATOR;
				$export_file_name = 'addons';
				break;

			default:
				$mypath = is_dir($_path)? $_path: realpath(root).DIRECTORY_SEPARATOR;
				break;
		}


		$directory   = new \RecursiveDirectoryIterator($mypath);
		$flattened   = new \RecursiveIteratorIterator($directory);

		// Make sure the path does not contain "/.Trash*" folders and ends eith a .php or .html file
		$files       = new \RegexIterator($flattened, "/\\.html\$/i");
		$translation = array();

		foreach($files as $file)
		{
			// create an record for array name
			switch ($_path)
			{
				case 'addons':
					$trans_key = substr($file, strpos($file, core_name)+mb_strlen(core_name)+1 );
					break;

				default:
					$trans_key = substr($file, strpos($file, db_name)+mb_strlen(db_name)+1 );
					break;
			}
			$file_name = basename($file,'.html');
			$lines     = file($file);
			$find      = "trans";
			$count     = 0;

			foreach($lines as $num => $line)
			{
				if(strpos($line, $find) !== false && strpos($line, "transparent") === false)
				// if(!preg_match("/\btrans\b/i", $line))
				{
					$count += 1;
					// find all matches with my creteria
					preg_match_all('/{% ?trans\s\"(.*?)\" ?%}/s', $line, $matches);
					// var_dump($matches);
					$translation[$trans_key] = 'New File';
					foreach ($matches[1] as $key => $value)
					{
						$value = $value;
						if($value)
						{
							$translation[$value] = 'Line '.($num+1);
							$count -= 1;
						}
					}
					preg_match_all("/\{\s*%\s*trans\s*%\s*}(.+?)\{\s*%\s*endtrans\s*%\s*}/", $line, $matches2);
					// var_dump($matches2);
					foreach ($matches2[1] as $key => $value)
					{
						$value = $value;
						if($value)
						{
							$translation[$value] = 'Line '.($num+1).' Seperate';
							$count -= 1;
						}
					}
				}
			}
			if($count === 0 )
			{
				// echo($trans_key.'<br/>');
				// unset($translation[$trans_key]);
			}
		}


		echo('<h2>Translation Export '.count($translation).' String</h2>');
		echo "<hr /><ol style='list-style-type:decimal;padding:0 15px;'>";


		// create translation file
		$translation_output  = '<?php'."\n".'private function transtext()'."\n{\n";
		foreach ($translation as $key => $value)
		{
			if($value=='New File')
			{
				@$translation_output .= "\n\t//".str_repeat('-', 80-mb_strlen($key))."$key\n";
				echo("</ul>");
				echo("<li>".$key.'</li>');
				echo("<ul style='margin-bottom:10px;'>");
			}
			else
			{
				@$translation_output .= "\t".'echo T_("'.$key.'");'.str_repeat(' ',70-mb_strlen($key)).'// '.$value."\n";
				echo("<li style='list-style-type:square;'>".$key.'</li>');
			}
		}
		$translation_output .= "\n}\n?>";
		file_put_contents(root. "/includes/languages/trans_".$export_file_name.".php", $translation_output);
		if($_path ==='addons')
		{
			if($_update === 'dash')
			{
				file_put_contents(addons. "includes/languages/trans_".$export_file_name.".php", $translation_output);
			}
		}

		echo "</ol><br/><br/><hr/><h1>Finish..!</h1>";
		if($_update === 'dash')
		{
			echo "<strong>Translation file in dash repository is updated</strong>";
		}
		echo "<p class='alert alert-success'>Extract string from twig file completed!</p></body></html>";
	}
}
