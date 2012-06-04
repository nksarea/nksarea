<?php

class rar extends base implements plugin
{

	public $methods = array('parseContent');
	public $output = array();
	private $results = array('SUCCESS', 'WARNING', 'FATAL ERROR', 'CRC ERROR', 'LOCKED ARCHIVE', 'WRITE ERROR', 'OPEN ERROR', 'USER ERROR', 'MEMORY ERROR', 'CREATE ERROR', 255 => 'USER BREAK');

	public function __construct() {}

	public function method($name, $par1, $par2, $par3, $par4, $par5)
	{
		switch ($name)
		{
			case 'parseContent':
				return $this->parseContent();
				break;
		}
	}

	public function returnCode($code)
	{
		if (!isset($this->results[$code]))
			return 'UNKNOWN';
		return $this->results[$code];
	}

	private function parseContent()
	{
		$content = array();
		$key = 0;

		if (!is_array($this->output))
			return $this->throwError('$this->output isn`t an array');

		while ($key < count($this->output) && !strpos($this->output[$key], '.....A.') && !strpos($this->output[$key], '.D.....'))
			$key++;

		for ($key; $key < count($this->output); $key += 2)
		{
			if (strpos($this->output[$key], '.....A.'))
				$temp = 'file';
			else if (strpos($this->output[$key], '.D.....'))
				$temp = array();
			else
				continue;

			$path = explode('\\', $this->output[$key - 1]);
			$path = array_reverse($path);

			foreach ($path as $value)
				$temp = array($value => $temp);

			$content = array_merge_recursive($temp, $content);
		}

		if (count($content) == 0)
			return false;
		return $content;
	}

}

?>
