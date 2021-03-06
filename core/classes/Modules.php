<?php

class Modules
{
	var $mods = array();
	private $bot;
	
	function __construct($bot)
	{
		$this->bot =& $bot;
	}

	function load($cdir, $ext="php")
	{
		$cdirname = $cdir;
		$cdir = scandir($cdir);
		foreach ($cdir as $c)
		{
			$pos = (strlen($ext) * -1);
			if (substr($c, $pos)==$ext)
			{
				$class = substr($c, 0, strpos($c, ".$ext"));
				require_once($cdirname.$class.'.'.$ext);
				$this->mods[$class] = new $class($this->bot->Event->evts);
			}
		}
	}
}

?>
