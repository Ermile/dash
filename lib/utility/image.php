<?php
namespace lib\utility;

/** Images manipulation class **/
class image
{
	/**
	 * True if an image is loaded, false otherwise
	 * @var bool
	 */
	private static $loaded = false;

	/**
	 * Image resource
	 * @var resource
	 */
	private static $img;

	/**
	 * Width of the image
	 * @var int
	 */
	private static $width;

	/**
	 * Height of the image
	 * @var int
	 */
	private static $height;

	/**
	 * Type of the image, relative to IMAGETYPE_* constants
	 * @var int
	 */
	private static $type;

	/**
	 * Quality of the image in percentage (JPEG only)
	 * @var int
	 */
	private static $quality = 95;


	/**
	 * Loads an image file
	 *
	 * @param string $filepath	Path of the image file
	 */
	public static function load($filepath)
	{
		self::$loaded = false;
		if(file::exists($filepath))
		{
			list(self::$width, self::$height, $type) = @getimagesize($filepath);
			// unset(self::$img);
			self::$img = null;
			if($type==IMAGETYPE_JPEG)
			{
				self::$img = @imagecreatefromjpeg($filepath);
			}
			else if($type==IMAGETYPE_GIF)
			{
				self::$img = @imagecreatefromgif($filepath);
			}
			else if($type==IMAGETYPE_PNG)
			{
				self::$img = @imagecreatefrompng($filepath);
			}
			if(!isset(self::$img))
				throw new \RuntimeException('Error opening image file "'.$filepath.'"');
			if(!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
				throw new \RuntimeException('Unsupported image file type : "'.$filepath.'"');

			self::$type = $type;

			// Preservation of the transparence / alpha for PNG and GIF
			if($type==IMAGETYPE_GIF || $type==IMAGETYPE_PNG){
				imagealphablending(self::$img, false);
				imagesavealpha(self::$img, true);
			}

			self::$loaded = true;
		}
		else
		{
			throw new \RuntimeException('The image file "'.$filepath.'" doesn\'t exist');
		}
	}


	/**
	 * Writes the image file from the image resource
	 *
	 * @param string $filepath	Path of the image file
	 */
	public static function save($filepath)
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');
		if(self::$type==IMAGETYPE_JPEG)
		{
			imagejpeg(self::$img, $filepath, self::$quality);
		}
		else if(self::$type==IMAGETYPE_GIF)
		{
			imagegif(self::$img, $filepath);
		}
		else if(self::$type==IMAGETYPE_PNG)
		{
			imagepng(self::$img, $filepath);
		}
	}

	/**
	 * Set the quality of the image (JPEG only)
	 *
	 * @param int $quality
	 */
	public static function setQuality($quality)
	{
		self::$quality = $quality;
	}

	/**
	 * Creates an new image resource
	 *
	 * @param int $width	Width of the image
	 * @param int $height	Height of the image
	 * @param bool $alpha	Activates alpha channel if true (optionnal)
	 * @return resource	New image resource
	 */
	private static function create($width, $height, $alpha=null)
	{
		if(!isset($alpha))
			$alpha = self::$type==1 || self::$type==3;

		$img = imagecreatetruecolor($width, $height);

		// Preservation of the transparence / alpha for PNG and GIF
		if($alpha)
		{
			imagealphablending($img, false);
			imagesavealpha($img, true);
		}

		return $img;
	}

	/**
	 * Crops the image
	 *
	 * @param int $src_x	X-coordinate of source point
	 * @param int $src_x	Y-coordinate of source point
	 * @param int $src_w	Width of source
	 * @param int $src_h	Height of source
	 * @param int $dst_w	Width of destination
	 * @param int $dst_h	Height of destination
	 */
	public static function crop($src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h)
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');

		$img = self::create($dst_w, $dst_h);

		imagecopyresampled($img , self::$img, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		imagedestroy(self::$img);
		self::$img = $img;
		self::$width = $dst_w;
		self::$height = $dst_h;
	}

	/**
	 * Resizes the image
	 *
	 * @param int $width	New width
	 * @param int $height	New height
	 */
	public static function resize($width, $height)
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');
		self::crop(0, 0, self::$width, self::$height, $width, $height);
	}

	/**
	 * Resizes the width the image
	 *
	 * @param int $width	New width
	 * @param bool $prop	Keeps the proportions if true
	 */
	public static function setWidth($width, $prop=false)
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');
		if($prop)
		{
			$height = round(self::$height*$width/self::$width);
		}
		else
		{
			$height = self::$height;
		}
		self::$resize($width, $height);
	}

	/**
	 * Resizes the height the image
	 *
	 * @param int $height	New height
	 * @param bool $prop	Keeps the proportions if true
	 */
	public static function setHeight($height, $prop=false)
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');
		if($prop)
		{
			$width = round(self::$width*$height/self::$height);
		}
		else
		{
			$width = self::$width;
		}
		self::$resize($width, $height);
	}

	/**
	 * Miniaturizes the image to the wanted size, with cropping
	 *
	 * @param int $width	Width of the thumb
	 * @param int $height	Height of the thumb
	 */
	public static function thumb($width=null, $height=null)
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');

		if(!isset($width) && !isset($height))
			return false;
		if(!isset($width))
			$width = round(self::$width*$height/self::$height);
		if(!isset($height))
			$height = round(self::$height*$width/self::$width);

		$ratio_ex = self::$width / self::$height;
		$ratio = $width / $height;

		if($ratio_ex < $ratio)
		{
			$height_ratio = self::$width / $ratio;
			$height_half_diff = round((self::$height - $height_ratio) / 2);
			self::crop(0, $height_half_diff, self::$width, $height_ratio, $width, $height);
		}
		else
		{
			$width_ratio = self::$height * $ratio;
			$width_half_diff = round((self::$width - $width_ratio) / 2);
			self::crop($width_half_diff, 0, $width_ratio, self::$height, $width, $height);
		}
	}


	/**
	 * Returns the width of the image
	 *
	 * @return int
	 */
	public static function getWidth()
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');
		return self::$width;
	}

	/**
	 * Returns the height of the image
	 *
	 * @return int
	 */
	public static function getHeight()
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');
		return self::$height;
	}

	/**
	 * Returns the type of the image, relative to IMAGETYPE_* constants
	 *
	 * @return int
	 */
	public static function getType()
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');
		return self::$type;
	}

	/**
	 * Change the type of the image (using IMAGETYPE_* constants)
	 *
	 * @param int $type		IMAGETYPE_JPEG, IMAGETYPE_GIF, or IMAGETYPE_PNG
	 */
	public static function setType($type)
	{
		if(!self::$loaded)
			throw new \RuntimeException('No image loaded');
		if(in_array($type, array(IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG)))
			self::$type = $type;
	}
}
