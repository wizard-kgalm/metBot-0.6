<?php
class module
{
	var $sysname;
	var $name;
	var $version;
	var $info;
	var $switch = "on";
	var $commands;
	private $evts;

	final function __construct(&$evts)
	{
		$this->evts =& $evts;
		$this->main();
	}

	final function addCmd($command, $privs=0, $help=NULL)
	{
		$this->commands[$command] = new command($privs, $help);
	}

	final function hook($func, $event)
	{
		$f = get_class($this).'::'.$func;
		if (isset($this->evts[$event]))
		{
			if (!is_array($this->evts[$event]))
			{
				$last_func = $this->evts[$event];
				$this->evts[$event] = array($last_func, $f);
			}
			else
			{
				$this->evts[$event][] = $f;
			}
		}
		else
		{
			$this->evts[$event] = $f;
		}
	}
	
	final function unhook($func, $event)
	{
		$f = get_class($this).'::'.$func;
		if (isset($this->evts[$event]))
		{	
			if (is_array($this->evts[$event]))
			{
				if (in_array($f, $this->evts[$event]))
				{
					foreach($this->evts[$event] as $key => $func)
					{
						if ($this->evts[$event][$key] == $func)
							unset($this->evts[$event][$key]);
					}
				}
				else return false;
			}
		}
	}
	
	final function setMethod($command, $method)
	{
		if (isset($this->commands[$command]))
		{
			$this->commands[$command]->method = $method;
		}
		else return false;
	}
		

	function main()
	{
		throw new Exception(get_class($this).": module does not define its own `main` method");
	}
	
	final function save($data, $filename)
	{
		is_dir("./data/module/".get_class($this)) || mkdir("./data/module/".get_class($this));
		$contents = serialize($data);
		$f = fopen("./data/module/".get_class($this)."/$filename.bot", "w");
		fwrite($f, $contents);
		fclose($f);
	}
	
	final function load(&$var, $filename)
	{
		$var = @unserialize(@file_get_contents("./data/module/".get_class($this)."/$filename.bot"));
	}
}
?>
