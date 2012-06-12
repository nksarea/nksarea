<?php

class base
{
	// Typ-Konstanten, werden bei polyfunktionellen Funktionen gebraucht, um den
	// Typ des zu behandelnden Objektes anzugeben
	const TYPE_PROJECT = 1;
	const TYPE_FILE = 2;
	const TYPE_LIST = 3;

	public function template($template, $input)
	{
		$matches = array();
		$extension = explode('.', $template);
		$extension = $extension[count($extension) - 1] . '/';
		$template = SYS_TEMPLATE_FOLDER . $extension . $template;

		if (!is_array($input))
			return $this->throwError('$input isn`t an array');
		if (!isset($template))
			return $this->throwError('$template wasn`t defined');
		if (!is_file($template))
			return $this->throwError('$template is no file', $template);

		$templ = fopen($template, 'r');
		$template = fread($templ, filesize($template));
		fclose($templ);

		foreach ($input as $key => $value)
		{
			if (is_array($value))
			{
				if (!preg_match("/\%\[$key,(.*)\]\%/", $template, $matches))
					continue;

				$match = explode(',', $matches[1]);
				if (!is_array($match) || count($match) != 2)
					continue;

				foreach ($value as $k => $v)
					$temp[] = str_replace('$key', $k, str_replace('$value', $v, $match[0]));

				$value = implode($temp, $match[1]);
				$template = str_replace($matches[0], $value, $template);
			}
			if(is_bool($value))
			{
				$replace = '';

				if (!preg_match("/\%\¦$key,(.*)\¦\%/", $template, $matches))
					continue;
				if($value === true)
					$replace = $matches[1];
				
				$template = str_replace($matches[0], $replace, $template);
			}
			else
			{
				$template = str_replace('%{' . $key . '}%', $value, $template);
			}
		}

		preg_match_all('/\%\|(.+)\|\%/', $template, $matches);
		foreach ($matches[1] as $key => $value)
		{
			$match = explode(',', $value);
			if (!is_array($match) || count($match) != 2)
				continue;

			$match = $this->template($match[0], array('insert' => $match[1]));
			if (!$match)
				continue;

			$template = str_replace($matches[0], $match, $template);
		}

		if (strpos($template, '%{') || strpos($template, '%[') || strpos($template, '%|') || strpos($template, '%¦'))
			return $this->throwError('Not every field in the template had been replaced', $template);

		return $template;
	}

	public function throwError($text, $var = NULL)
	{
		if (!is_string($text))
			$text = 'Error text wasn\'t a string!';
		if ($var != NULL)
			var_dump($var);

		trigger_error($text);
		exit;
	}

	public function throwWarning($text)
	{
		if (!isset($GLOBALS['warning']))
			$this->throwError('Not using base.class properly');

		if (!is_string($text))
			$this->throwError('Warning text wasn\'t a string', $text);
		else
			$GLOBALS['warning'][] = $text;
		return false;
	}

}
