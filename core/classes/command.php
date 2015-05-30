<?php
class command
{
	var $method;
	var $privs;
	var $help;
	var $switch = "on";
	
	function __construct($privs, $help)
	{
		$this->privs = $privs;
		$this->help = $help;
	}
}
?>
