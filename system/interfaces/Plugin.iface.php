<?php
interface Plugin
{
	public function __construct();

	public function method($name, $par1, $par2, $par3, $par4, $par5);

	public function returnCode($code);

	
	public function template($template, $input);

	public function throwError($text, $var = NULL);

	public function throwWarning($text);
}
