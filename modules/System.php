<?php
class System extends module
{
	var $sysname = "System";
	var $name = "System Commands";
	var $version = "1.5";
	var $info = "These are the default system commands.";
	
	function main()
	{
		$this->addCmd('about', 0, "This tells you about the bot.");
		$this->addCmd('commands', 0, "This shows you the bot's commands.<sub><ul><li>Type <b>{trigger}commands</b> to show the commands you have access to.</li><li>Type <b>{trigger}commands all</b> to show all the commands the bot has.</li><li>Type <b>{trigger}commands privs</b> to show commands grouped by the minimum required privclass level needed to use them.</li></ul>");
		$this->addCmd('help', 0, "This gives you help on the commands that have help documentation. Used <b>{trigger}help <i>command</i></b>");
		$this->addCmd('module', 0, "This command shows info about the bot's modules.<sub><ul><li>Type <b>{trigger}module info <i>module</i></b> to get info on the module <i>module</i>.</li><li>Type <b>{trigger}module on/off <i>module</i></b> to turn the module <i>module</i> on and off.</li></ul></sub>");
		$this->addCmd('modules', 0, "This lists the modules in the bot.");
		$this->addCmd('quit', 75, "This makes your bot quit dAmn.");
		$this->addCmd('restart', 75, "This makes your bot restart completely.");
		$this->addCmd('sudo', 100, "Does a command as another user. Used <b>{trigger}sudo <i>person</i> <i>command</i> <i>arguments</i></b>. Ex: <code>{trigger}sudo Noobobob123 away Noobob is away</code> would run as if Noobob123 said <code>{trigger}away Noobob is away</code>.");
		$this->addCmd('autojoin', 75, "Manage the list of autojoined channels.<sub><ul><li>Use <b>{trigger}autojoin list</b> to show the list of autojoined channels.</li><li>Use <b>{trigger}autojoin add <i>channel</i></b> to add #<i>channel</i> to the bot's autojoin list.</li><li>Use <b>{trigger}autojoin del <i>channel</i></b> to remove #<i>channel</i> from the bot's autojoin list.</li></ul></sub>");
		$this->addCmd('trig', 75, "Changes the bot's trigger.<sub><ul><li>Use <b>{trigger}trig <i>!</i> y</b> to temporarily change your trigger to <i>!</i>, where <i>!</i> represents what you want to change your trigger to.</li><li>Use <b>{trigger}trig <i>!</i> save</b> to change your trigger to <i>!</i> and save it.");
		$this->addCmd('warn', 50, "Turns command warnings on and off. Ex: You try to do a command you don't have the privs to, and the bot tells you that you don't have the privs to if warnings are on. Use <b>{trigger}warn on/off</b> to turn warnings on or off.");
	}

	function c_about($cmd, $bot)
	{
		$uptime = $bot->uptime();
		$bot->dAmn->say("<abbr title=\"$cmd->from\"></abbr>Hello! I'm running <b><a href=\"http://www.botdom.com/wiki/". $bot->name ."\" title=\"". $bot->name." ".$bot->version."\">".$bot->name ." ". $bot->version."</a></b> by <b>:devmegajosh2:</b>. My owner is <b>:dev". $bot->admin .":</b>. I've been up for $uptime.", $cmd->ns);
	}

	function c_commands($cmd, $bot)
	{
		if ($cmd->args != "privs")
		{
			$unusable = array();
			$commands_info = array();
			ksort($bot->Modules->mods);
			if ($cmd->args == "all")
			{
				$all = true;
			}
			else
			{
				$all = false;
			}

			$not_privd = 0;
			foreach($bot->Modules->mods as $mod => $val)
			{
				foreach($val->commands as $key => $value)
				{
					$commands_info[$key] = $value;
					$commands_info[$key]->module = $mod;
					if (!$all)
					{
						if (!$bot->Commands->has_privs($cmd->from, $key))
							$not_privd++;
					}
				}
				if ($not_privd == count($val->commands))
					$unusable[] = $mod;
				$not_privd = 0;
			}

			$txt = $all == false?"<abbr title=\"$cmd->from\"></abbr>You have access to the following commands. Commands that are <s>striked</s> are disabled.<br><br><sub>":"<abbr title=\"$cmd->from\"></abbr>Commands that are <s>striked</s> are disabled.<br><br><sub>";
			$cmds = array();
			foreach($bot->Modules->mods as $mod => $val)
			{
				if (in_array($mod, $unusable))
				{
					continue;
				}
				$txt .= "<b><u>".$val->sysname."</u></b>: ";
				foreach($commands_info as $key => $value)
				{
					if (!isset($commands_info[$key]->module)) continue;
					if ($commands_info[$key]->module == $mod)
					{
						if ($commands_info[$key]->switch == "off" || $bot->Modules->mods[$mod]->switch == "off")
						{
							if (!$all && !$bot->Commands->has_privs($cmd->from, $key)) $txt .= '';
							else
							{
								$cmds[$key] = "<abbr title=\"privs: ". $commands_info[$key]->privs ."\"><s>$key</s></abbr>";
							}
						}
						elseif ($commands_info[$key]->switch == "on" && $bot->Modules->mods[$mod]->switch == "on")
						{
							if (!$all && !$bot->Commands->has_privs($cmd->from, $key)) $txt.= '';
							else
							{
								$cmds[$key] = "<abbr title=\"privs: ". $commands_info[$key]->privs ."\">$key</abbr>";
							}
						}
						unset($commands_info[$key]->module);
					}
				}
				ksort($cmds);
				$txt .= join(', ', $cmds);
				$txt .= "<br>";
				$cmds = array();
			}
			$txt .= "<br>Type <code>". $bot->trigger ."help </code><i><code>command</code></i> to get help on a command.<br>Type <code>".$bot->trigger."commands privs</code> to see commands grouped by privilege level.</sub>";
			$txt .= $all == false? "<sub><br>Type <code>". $bot->trigger ."commands all</code> to see commands you do not have access to as well.</sub>" : '';
			$bot->dAmn->say($txt, $cmd->ns);
		}
		elseif($cmd->args == "privs")
		{
			$txt = "<abbr title=\"$cmd->from\"></abbr>Commands that are <s>striked</s> are disabled.<br><br><sub>";
			$cmds = array();
			$used_levels = array();
			
			foreach($bot->Modules->mods as $mod => $val)
			{
				foreach($val->commands as $key => $value)
				{
					$commands_info[$key] = $value;
					$commands_info[$key]->module = $mod;
					if (!in_array($val->commands[$key]->privs, $used_levels))
					{
						$used_levels[] = $val->commands[$key]->privs;
					}
				}
			}
			
			foreach($bot->levels as $lvl => $name)
			{
				if (!in_array($lvl, $used_levels)) continue;
				$txt .= "<b><u>$name ($lvl):</u></b> ";
				foreach ($commands_info as $command => $info)
				{
					if ($info->privs == $lvl)
					{
						$cmds[] = "<abbr title=\"$info->module\">$command</abbr>";
					}
				}
				$txt .= join(', ', $cmds);
				$txt .= '<br>';
				$cmds = array();
			}
			$txt .= "<br>Type <code>". $bot->trigger ."help </code><i><code>command</code></i> to get help on a command.</sub>";
			//$txt .=  "<sub><br>Type <code>". $bot->trigger ."commands all</code> to see commands you do not have access to as well.</sub>" ;
			$bot->dAmn->say($txt, $cmd->ns);
		}
	}

	function c_help($cmd, $bot)
	{
		$help_text = NULL;
		if(strlen($cmd->args) > 0)
		{
			$help_command = $cmd->arg(0);
	
			if (count(explode(' ', $cmd->args)) > 1)
			{
				$cmd->from = $cmd->arg(1, true);
			}
			
			if ($help_command == $bot->say)
			{
				$bot->dAmn->say("<abbr title=\"$cmd->from\"></abbr><b>say:</b> Make your bot say something in the specified channel. The #<i>channel</i> parameter is optional, and if not specified the bot will say the message in the current chatroom.<sub><ul><li>Type <code></code>$bot->say [<i>#channel</i>] <i>message</i> to say <i>message</i> in <i>#channel</i>.<li>If you type \"/me\" before your message like in the official dAmn client, your bot will do an action.</li></ul>", $cmd->ns);
				return;
			}

			if ($help_command == $bot->exec || $help_command == "$bot->exec:")
			{
				$bot->dAmn->say("<abbr title=\"$cmd->from\"></abbr><b>exec:</b> Execute PHP code. If the code returns anything, the bot will say it in the channel the code was executed in.<sub><ul><li>Type $bot->exec <i>php code</i> to execute <i>php code</i> with the bot.</li><li>Type $bot->exec: <i>php code</i> to prepend <i>php code</i> with a return statement. Ex: $bot->exec: 3 + 2; would be the equivalent of $bot->exec return 3 + 2;</li></ul>", $cmd->ns);
				return;
			}

			foreach($bot->Modules->mods as $key => $value)
			{
				if (isset($bot->Modules->mods[$key]->commands[$help_command]->help))
				{
					$help_text = $bot->Modules->mods[$key]->commands[$help_command]->help;
				}
			}
			
			if (preg_match("/^[aeiouy]/", $help_command))
			{
				$a = "an";
			}
			else
			{
				$a = "a";
			}
			
			if ($help_text == null)
			{
				$bot->dAmn->say("$cmd->from: There is no help text for $a $help_command command.", $cmd->ns);
				return;
			}
			$help_text = str_replace('{trigger}', $bot->trigger, $help_text);
			$bot->dAmn->say("<abbr title=\"". $cmd->from ."\"></abbr><b>". $help_command .":</b> ". $help_text, $cmd->ns);
		}
		else
		{
			$bot->dAmn->say("$cmd->from: Please specify what command you would like help with.", $cmd->ns);
		}
	}

	function c_modules($cmd, $bot)
	{
		$module_names = array_keys($bot->Modules->mods);
		ksort($module_names);
		$txt = "Modules that are <s>striked</s> are turned off.<br><sub><b>";
		foreach($module_names as $mod)
		{
			$sysname = $bot->Modules->mods[$mod]->sysname;
			if ($bot->Modules->mods[$mod]->switch == "on")
			{
				$a[] = "&#171; $sysname &#187;";
			}
			if ($bot->Modules->mods[$mod]->switch == "off")
			{
				$a = "&#171; <s>$sysname</s> &#187;";
			}
		}
		$txt .= join(' ', $a);
		$txt .= "</b></sub>";

		$bot->dAmn->say("<abbr title=\"$cmd->from\"></abbr>$txt", $cmd->ns);
	}
	
	function c_module($cmd, $bot)
	{
		$command = $cmd->arg(0);
		$module = $cmd->arg(1);
		
		if ($command != -1)
		{
			if ($command == "info")
			{
				if ($module != -1)
				{
					$name = '';
					$version = '';
					$info = '';
					$switch = '';
					$commands = '';
					$found = false;
					foreach($bot->Modules->mods as $key => $mod)
					{
						if ($mod->sysname == $module)
						{
							$name = $mod->name;
							$version = $mod->version;
							$info = $mod->info;
							$switch = $mod->switch;
							$commands = $mod->commands;
							$found = true;
							break;
						}
					}
					if ($found)
					{
						$txt .= "<sub><b><u>$module</u></b></sub><br>";
						$txt .= "<b>Name:</b> $name<br>";
						$txt .= "<b>Version:</b> $version<br>";
						$txt .= "<b>Info:</b> $info<br>";
						$txt .= "<sub>This module is <b>$switch</b>.<br><br>";
						$txt .= "<b><u>Commands:</u></b><br>";
						foreach($commands as $command => $the)
						{
							if ($the->switch == "on")
							{
								$txt .= "<abbr title=\"privs: ". $the->privs ."\"><b>$command</b></abbr>, ";
							}
							else
							{
								$txt .= "<abbr title=\"privs: ". $the->privs ."\"><b><s>$command</s></b></abbr>, ";
							}
						}
						$bot->dAmn->say("<abbr title=\"$cmd->from\"></abbr>$txt", $cmd->ns);
					}
					else
					{
						$bot->dAmn->say("$cmd->from: There is no module called $module.", $cmd->ns);
					}
				}
				else
				{
					$bot->dAmn->say("$cmd->from: You must set a module to get info from.", $cmd->ns);
				}
			}
			elseif ($command == "on")
			{
				if ($module != -1)
				{
					if ($bot->privs[$cmd->from] < 75)
					{
						$bot->dAmn->say("$cmd->from: You do not have the priv level to turn on modules.", $cmd->ns);
						return;
					}
					$found = true;
					$already_on = false;
					foreach ($bot->Modules->mods as $key => $mod)
					{
						if ($module == $mod->sysname)
						{
							if ($mod->switch == 'on')
								$already_on = true;
							else
							{
								$bot->Modules->module[$module]->switch = 'on';
								$found = true;
							}
						}
					}
					
					if ($found == true)
					{
						if ($already_on == true)
						{
							$bot->dAmn->say("$cmd->from: $module is already on.", $cmd->ns);
						}
						else
						{
							$bot->dAmn->say("$cmd->from: Module <b>$module</b> is now turned on.", $cmd->ns);
						}
					}
				}
				else
				{
					$bot->dAmn->say("$cmd->from: You must specify a module to turn on.", $cmd->ns);
				}
			}
			elseif ($command == "off")
			{
				if ($module != -1)
				{
					if ($bot->privs[$cmd->from] < 75)
					{
						$bot->dAmn->say("$cmd->from: You do not have the privs to turn off modules.", $cmd->ns);
						return;
					}
					$found = true;
					$already_off = false;
					foreach ($bot->Modules->mods as $key => $mod)
					{
						if ($module == $mod->sysname)
						{
							if ($mod->switch == 'off')
								$already_off = true;
							else
							{
								$bot->Modules->mods[$module]->switch = 'off';
								$found = true;
							}
						}
					}
					
					if ($found == true)
					{
						if ($already_off == true)
						{
							$bot->dAmn->say("$cmd->from: $module is already off.", $cmd->ns);
						}
						else
						{
							$bot->dAmn->say("$cmd->from: Module <b>$module</b> is now turned off.", $cmd->ns);
						}
					}
				}
				else
				{
					$bot->dAmn->say("$cmd->from: You must specify a module to turn off.", $cmd->ns);
				}
			}
			else
			{
				
				$bot->dAmn->args = "module";
				$bot->Commands->execute("help");
			}
		}
		else
		{
			
			$bot->dAmn->args = "module";
			$bot->Commands->execute("help");
		}
	}
	
	function c_quit($cmd, $bot)
	{
		$uptime = $bot->uptime();
		$bot->dAmn->say("$cmd->from: Quitting dAmn. Uptime: $uptime", $cmd->ns);
		$fp = fopen('./core/status/close.bot', 'w');
		fclose($fp);
		$bot->dAmn->send("disconnect\n\0");
		$bot->quit = true;
	}

	function c_restart($cmd, $bot)
	{
		
		$uptime = $bot->uptime();
		$bot->dAmn->say("$cmd->from: Restarting bot. Uptime: $uptime", $cmd->ns);
		$fp = fopen('./core/status/restart.bot', 'w');
		fclose($fp);
		$bot->dAmn->send("disconnect\n\0");
		$bot->Console->msg("Restarting bot...");
		$bot->quit = true;
	}
	
	function c_sudo($cmd, $bot)
	{
		
		$bot->dAmn->from = $cmd->arg(0);
		$command = $cmd->arg(1);
		$bot->dAmn->args = $cmd->arg(2, true);
		$bot->Commands->execute($command);
	}
	
	function c_autojoin($cmd, $bot)
	{
		$command = $cmd->arg(0);
		$channel = $cmd->arg(1);
		if ($command != -1)
		{
			if ($command == "add")
			{
				if ($channel != -1)
				{
					$bot->join[] = $channel;
					$bot->dAmn->say("$cmd->from: Channel <b>". $bot->dAmn->deform($channel) ."</b> was added to my autojoin list.", $cmd->ns);
					$bot->saveConfig();
				}
				else
				{
					$bot->dAmn->say("$cmd->from: You must specify a channel to add.", $cmd->ns);
				}
			}
			elseif ($command == "del")
			{
				if ($channel != -1)
				{
					if (in_array($channel, $bot->join))
					{
						foreach ($bot->join as $key => $j)
						{
							if ($j == $channel)
								unset($bot->join[$key]);
						}
						$bot->saveConfig();
						$bot->dAmn->say("$cmd->from: Channel <b>". $bot->dAmn->deform($channel) ."</b> was removed.", $cmd->ns);
					}
					else
					{
						$bot->dAmn->say("$cmd->from: ". $bot->dAmn->deform($channel) ." isn't on my autojoin list.", $cmd->ns);
					}
				}
				else
				{
					$bot->dAmn->say("$cmd->from: You must specify a channel to delete.", $cmd->ns);
				}
			}
			elseif ($command == "list")
			{
				$chans = '';
				foreach ($bot->join as $j)
				{
					$chans[]= "".$bot->dAmn->deform($j);
				}
				$bot->dAmn->say("<abbr title=\"$cmd->from\"></abbr><sub><u><b>Autojoin list:</b></u><br>".join("\n", $chans), $cmd->ns);
			}
			else
			{
				
				$bot->dAmn->args = "autojoin";
				$bot->Commands->execute("help");
			}
		}
	}
	
	function c_trig($cmd, $bot)
	{
		$trig = $cmd->arg(0);
		$confirm = $cmd->arg(1);
		if ($trig != -1)
		{
			if ($confirm != -1)
			{
				if ($confirm == "y")
				{
					$bot->trigger = $trig;
					$bot->dAmn->say("$cmd->from: Trigger temporarily changed to <code>$trig</code>.", $cmd->ns);
				}
				elseif ($confirm == "save")
				{
					$bot->trigger = $trig;
					$bot->saveConfig();
					$bot->dAmn->say("$cmd->from: Trigger saved as and changed to <code>$trig</code>.", $cmd->ns);
				}
				else
				{
					
					$bot->dAmn->args = "trig";
					$bot->Commands->execute("help");
				}
			}
			else
			{
				$bot->dAmn->say("$cmd->from: You must confirm the trigger change. Type ".$bot->trigger."trig $trig y or ".$bot->trigger."trig $trig save to temporarily change your trigger OR change it and save it.", $cmd->ns);
			}
		}
		else
		{
			
			$bot->dAmn->args = "trig";
			$bot->Commands->execute("help");
		}
	}

	function c_warn($cmd, $bot)
	{
		$switch = strtolower($cmd->arg(0));
		if ($switch == "on" || $switch == "off")
		{
			if ($bot->warnings == ($switch=="on"?true:false))
			{
				$bot->dAmn->say("$cmd->from: Warnings are already $switch.", $cmd->ns);
			}
			else
			{
				$bot->warnings = ($switch=="on"?true:false);
				$bot->dAmn->say("$cmd->from: Warnings have been turned <b>$switch</b>.", $cmd->ns);
				$bot->saveConfig();
			}
		}
	}
}
?>
