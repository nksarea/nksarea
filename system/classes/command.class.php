<?php
class command extends base
{
	private $output;
	private $plugins = array();
	private $pluginMethods = array();

	public function __get($name)
	{
		switch ($name)
		{
			case 'output':
				return $this->$name;
				break;
		}
	}

	public function __construct()
	{
		$dir = opendir(SYS_PLUGIN_FOLDER);
		while (($file = readdir($dir)) !== false)
		{
			if (!strpos($file, '.plugin.php'))
				continue;

			$class = str_replace('.plugin.php', '', $file);
			include_once(SYS_PLUGIN_FOLDER . $file);
			$instance = new $class();

			if (!$instance instanceof Plugin)
				continue;
			$instance->output = &$this->output;
			foreach ($instance->methods as $value)
				$pluginMethods[] = $class . '_' . $value;

			$this->plugins[$class] = $instance;
		}
		closedir($dir);
	}

	public function execute($template, $input, $asynchron = false)
	{
		$path = SYS_TMP . uniqid();
		if (!mkdir($path))
			$this->throwError("Couldn`t create directory", $path);

		$template = $this->template($template . '.cmd', $input);
		if ($asynchron === true)
			$command = $this->template('mainAsy.cmd', array("insert" => $template, 'temp_dir' => $path));
		else
			$command = $this->template('main.cmd', array("insert" => $template, 'temp_dir' => $path));

		$file = fopen($path . '/command.bat', 'w');
		fwrite($file, $command);
		fclose($file);

		if ($asynchron === true)
		{
			pclose(popen('start "asy" "' . $path . '/command.bat"', "r"));
			return $path . '/output.txt';
		}
		exec($path . '/command.bat', $this->output);

		$result = $this->output[count($this->output) - 1];
		$result = explode(', ', $result);

		if ($result[0] == 1)
			return true;

		foreach ($result as $value)
		{
			$value = explode(':', $value);

			if (count($value) != 3)
				continue;
			if (isset($this->plugins[$value[2]]))
				$value[1] = $this->plugins[$value[2]]->returnCode($value[1]);

			$this->throwWarning("Error on line $value[0], $value[2] returned code $value[1]");
		}

		return false;
	}

	public function plugin($name, $par1 = NULL, $par2 = NULL, $par3 = NULL, $par4 = NULL, $par5 = NULL)
	{
		if (array_search($name, $this->pluginMethods))
			$this->throwError('Plugin not loaded or method missing', $name);

		$name = explode('_', $name);

		return $this->plugins[$name[0]]->method($name[1], $par1, $par2, $par3, $par4, $par5);
	}

}