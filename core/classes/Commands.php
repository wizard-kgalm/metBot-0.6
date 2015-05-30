<?php

class Commands
{
	var $code;
	private $bot;
	
	function __construct($bot)
	{
		$this->bot =& $bot;
	}
	
	function execute($command)
	{
		
		$found_command = false;
		foreach($this->bot->Modules->mods as $key => $value)
		{
			if (isset($this->bot->Modules->mods[$key]->commands[$command]))
			{
				$found_command = true;
				if ($this->bot->Modules->mods[$key]->switch == "off" && $this->bot->Event->cmd != $command)
				{
					if ($this->bot->warnings) $this->bot->dAmn->say("The module that the \"$command\" command is attached to is currently turned off.", $this->bot->dAmn->ns);
					return;
				}
				else if ($this->bot->Modules->mods[$key]->commands[$command]->switch == "on")
				{
					if ($this->has_privs($this->bot->dAmn->from, $command))
					{
						if ($this->bot->Modules->mods[$key]->commands[$command]->method == NULL)
							$command = "c_".$command;
						else
							$command = $this->bot->Modules->mods[$key]->commands[$command]->method;
						
						$cmd = new cmd($this->bot->dAmn->ns, $this->bot->dAmn->from, $this->bot->dAmn->args, $this->bot->Event->evt);
						$this->bot->Modules->mods[$key]->$command($cmd, $this->bot);
						return;
					}
					elseif ($this->bot->Event->cmd != $command)
					{
						if ($this->bot->warnings) $this->bot->dAmn->say("You do not have a high enough priv level to access this command. The minimum priv level to use this command is ". $this->bot->Modules->mods[$key]->commands[$command]->privs .".", $this->bot->dAmn->ns);
						return;
					}
				}
				else if ($this->bot->Modules->mods[$key]->commands[$command]->switch == "off")
				{
					if ($this->bot->warnings) $this->bot->dAmn->say("The \"$command\" command is currently turned off.", $this->bot->dAmn->ns);
					return;
				}
				else
				{
					if ($this->bot->warnings) $this->bot->dAmn->say("The \"$command\" command must be attached to a module.", $this->bot->dAmn->ns);
					return;
				}
			}
		}
		if ($found_command == false)
		{
			if ($this->bot->warnings) $this->bot->dAmn->say("There is no command \"$command\".", $this->bot->dAmn->ns);
		}
		$this->bot->dAmn->args = NULL;
	}
	
	function has_privs($user, $command)
	{
		foreach($this->bot->Modules->mods as $key => $value)
		{
			$level = 0;
			foreach($this->bot->privs as $lvl => $members)
			{
				if (in_array($user, $members))
				{
					$level = $lvl;
					break;
				}
			}
			
			if (isset($this->bot->Modules->mods[$key]->commands[$command]))
			{
				if ($this->bot->Modules->mods[$key]->commands[$command]->privs <= $level)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
	}
	
	function process($who, $str)
	{
		$this->bot->dAmn->from = $who;
		$name = substr($str, strlen($this->bot->trigger));
		$command = explode(' ', $name);
		$name = $command[0];
		$this->bot->dAmn->args = substr($str, strlen($this->bot->trigger.$name.' '));
		if (strlen($name) > 0)
		{
			if ($this->bot->dAmn->args == "?")
			{
				$this->bot->dAmn->args = $name;
				$this->execute('help');
			}
			else
			{
				$this->execute($name);
			}
		}
	}
}
