<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of diashow
 *
 * @author Lorze
 */
class diashow extends base
{

	private $json;
	private $folder;
	private $length = 0;
	private $config = array(
		'maxWidth' => 360,
		'maxHeight' => 600,
		'slideWidth' => 'fit',
		'slideHeight' => 'auto',
		'slideDistance' => 10,
		'slideHref' => true,
		'loadOffset' => 4
	);

	public function __construct($folder, $config = false)
	{
		$folder = SYS_DIASHOW_FOLDER . $folder . '/';
		if (!is_dir($folder))
			$this->throwError('The requested diashow isn`t stored on this server');

		if(is_array($config))
			$this->config = array_merge($this->config, $config);
		$this->folder = $folder;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'json':
				return $this->getJSON();
			case 'maxWidth':
			case 'maxHeight':
			case 'slideWidth':
			case 'slideHeight':
			case 'slideDistance':
			case 'slideHref':
			case 'loadOffset':
				return $this->config[$name];
			default:
				return false;
		}
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'picture':
				return $this->addPicture($value);
			case 'maxWidth':
			case 'maxHeight':
			case 'slideWidth':
			case 'slideHeight':
			case 'slideDistance':
			case 'slideHref':
			case 'loadOffset':
				$this->config[$name] = $value;
		}
	}

	private function getJSON()
	{
		if(is_string($this->json))
			return $this->json;
		
		$size = array('height' => 0, 'width' => 0);
		$json = array();
		$json['slides'] = array();

		$dir = opendir($this->folder);
		while ($img_name = readdir($dir))
		{
			if (!strpos($img_name, '.thumb.') && @getimagesize($this->folder . $img_name))
			{
				$thumb_name = preg_replace('/\.[A-Za-z0-9]{3}/', '.thumb.jpg', $img_name);
				if (!is_file($this->folder . $thumb_name))
				{
					if (!$this->createThumb($this->folder . $img_name, $this->folder . $thumb_name, $this->config['maxWidth'], $this->config['maxHeight']))
						return array();
				}

				$thumb_size = getimagesize($this->folder . $thumb_name);
				$json['slides'][] = array('width' => $thumb_size[0], 'height' => $thumb_size[1], 'src' => $this->folder . $thumb_name, 'href' => $this->folder . $img_name);

				if ($thumb_size[1] > $size['height'])
					$size['height'] = $thumb_size[1];
				if ($thumb_size[0] > $size['width'])
					$size['width'] = $thumb_size[0];
			}
		}

		if (count($json['slides']) == 0)
			$this->throwError('There isn`t any data for this diashow');

		$json['height'] = $size['height'];
		$json['width'] = $size['width'];
		$json['config'] = $this->config;

		$this->json = json_encode($json);
		return $this->json;
	}
	
	private function addPicture($file)
	{
		if(!is_file(SYS_TEMP_FOLDER . $file))
			$this->throwError('$file isn`t a file!');
		if($this->length == 0)
		{
			$dir = opendir($this->folder);
			while ($img_name = readdir($dir))
			{
				if(!strpos($img_name, '.thumb.') && @getimagesize($this->folder . $img_name))
					$this->length ++;
			}
			closedir($dir);
		}
		
		$extension = explode('.', $file);
		$extension = $extension[count($extension)];
		$this->json = NULL;
		$this->length ++;
		return getRAR()->execute('moveFile', array('source' => SYS_TEMP_FOLDER . $file, 'destination' => SYS_DIASHOW_FOLDER . $this->length . $extension));
	}

	private function createThumb($imgPath, $thumbPath, $maxWidth, $maxHeight)
	{
		if (!$type = getimagesize($imgPath))
			return false;
		$type = $type[2];
		$size = 1;

		switch ($type)
		{
			case "1": $imorig = imagecreatefromgif($imgPath);
				break;
			case "2": $imorig = imagecreatefromjpeg($imgPath);
				break;
			case "3": $imorig = imagecreatefrompng($imgPath);
				break;
			default: $imorig = imagecreatefromjpeg($imgPath);
		}

		$width = imagesx($imorig);
		$height = imagesy($imorig);

		$if_anzahl = 0;
		if ($maxWidth < $width && $maxWidth != 0)
		{
			$size = $maxWidth / $width;
		}
		if ($maxHeight < $height && $maxHeight != 0)
		{
			$size = $maxHeight / $height;
		}
		if ($size == 1)
		{
			return imagejpeg($imorig, $thumbPath);
		}
		$im = imagecreatetruecolor($width * $size, $height * $size);

		if (imagecopyresampled($im, $imorig, 0, 0, 0, 0, $width * $size, $height * $size, $width, $height))
		{
			return imagejpeg($im, $thumbPath);
		}
		return false;
	}

}

?>
