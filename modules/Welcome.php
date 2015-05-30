<?php
class Welcome extends module
{
	var $sysname = "Welcome";
	var $name = "Welcome";
	var $version = "1";
	var $info = "These commands are used to set welcomes.";
	var $welcomes = array();
	var $switches = array();
	var $indv = false;
	
	function main()
	{
		$this->addCmd("wt", 50, "Sets a welcome. The #<i>channel</i> parameter is optional. If you do not specify it, the welcome will be set for the current channel. In all welcomes, <code><b>{from}</b></code> is replaced with the name of the person that enters the room.<sub><ul><li>Use <b>{trigger}wt #<i>channel</i> on/off</b> to turn welcomes on and off.</li><li><b>Use {trigger}wt #<i>channel</i> all <i>message</i></b> to welcome everyone that enters #<i>channel</i> with <i>message</i></li><li>Use <b>{trigger}wt #<i>channel</i> pc <i>privclass</i> <i>message</i></b> to welcome everyone that joins and is in the <i>privclass</i> privclass with <i>message</i>.</li><li>Use <b>{trigger}wt #<i>channel</i> indv/individual <i>message</i></b> to let people set their own welcomes with the {trigger}welcome command, with their welcomes coming after the message <i>message</i>. You do not have to set an individual welcome message.</li>");
		$this->addCmd("welcome", 0, "When individual welcomes are on, this sets your welcome. Used <b>{trigger}welcome <i>message</i></b>.");
		$this->hook("do_welcome", "recv_join");
		$this->load($this->welcomes, "welcomes");
		$this->load($this->switches, "switch");
	}
	
	function c_wt($cmd, $bot)
	{
		$arg = $cmd->arg(0);
		if ($arg[0] == "#" || $arg[0] == "@")
		{
			$chan = $cmd->arg(0);
		}
		
		if (isset($chan))
		{
			if ($cmd->arg(1) == "all")
			{
				$msg = $cmd->arg(2, true);
				$this->welcomes[$bot->dAmn->format($chan)]['all'] = $msg;
				$bot->dAmn->say("$cmd->from: The welcome <i>\"$msg\"</i> was set for all people in <b>$chan</b>.", $cmd->ns);
				if ($this->switches[$bot->dAmn->format($chan)] != "on")
					$this->switches[$bot->dAmn->format($chan)] = "on";
				$this->save($this->welcomes, "welcomes");
				$this->save($this->switches, "switch");
			}
			elseif ($cmd->arg(1) == "pc")
			{
				$pc = $cmd->arg(2);
				$msg = $cmd->arg(3, true);
				$this->welcomes[$bot->dAmn->format($chan)]['pc'][$pc] = $msg;
				$bot->dAmn->say("$cmd->from: The welcome <i>\"$msg\"</i> was set for all people in the privclass <b>$pc</b> in <b>$chan</b>.", $cmd->ns);
				if ($this->switches[$bot->dAmn->format($chan)] != "on")
					$this->switches[$bot->dAmn->format($chan)] = "on";
					
				$this->save($this->welcomes, "welcomes");
				$this->save($this->switches, "switch");
			}
			elseif ($cmd->arg(1) == "indv" || $cmd->arg(1) == "individual")
			{
				$this->indv = true;
				if ($cmd->arg(2) == -1)
				{
					$bot->dAmn->say("$cmd->from: People can now set their own welcomes in <b>$chan</b>.", $cmd->ns);
					if ($this->switches[$bot->dAmn->format($chan)] != "on")
						$this->switches[$bot->dAmn->format($chan)] = "on";
						
					$this->save($this->welcomes, "welcomes");
					$this->save($this->switches, "switch");
				}
				else
				{
					$this->welcomes[$bot->dAmn->format($chan)]['indv']['all'] = $cmd->arg(2, true);
					$bot->dAmn->say("$cmd->from: People can now set their own welcomes in <b>$chan</b>, coming after the message <i>\"".$cmd->arg(2, true)."\"</i>.", $cmd->ns);
					if ($this->switches[$bot->dAmn->format($chan)] != "on")
						$this->switches[$bot->dAmn->format($chan)] = "on";
						
					$this->save($this->welcomes, "welcomes");
					$this->save($this->switches, "switch");
				}
			}
			elseif ($cmd->arg(1) == "on" || $cmd->arg(1) == "off")
			{
				if (is_array($this->switches))
				{
					$this->switches[$bot->dAmn->format($chan)] = $cmd->arg(1);
					$this->save($this->switches, "switch");
				}
				else
				{
					$this->switches = array($bot->dAmn->format($chan) => $cmd->arg(1));
					$this->save($this->switches, "switch");
				}
				$bot->dAmn->say("$cmd->from: Welcomes for $chan have been turned <b>".$cmd->arg(1)."</b>.", $cmd->ns);
			}
			elseif ($cmd->arg(1) == "del")
			{
				if ($cmd->arg(2) == "all")
				{
					if (isset($this->welcomes[$bot->dAmn->format($chan)]['all']))
					{
						unset($this->welcomes[$bot->dAmn->format($chan)]['all']);
						$bot->dAmn->say("$cmd->from: The global welcome for <b>$chan</b> was deleted.", $cmd->ns);
						$this->save($this->welcomes, "welcomes");
					}
					else
					{
						$bot->dAmn->say("$cmd->from: There is no global welcome for <b>$chan</b>", $cmd->ns);
					$this->save($this->welcomes, "welcomes");
					}
				}
				elseif ($cmd->arg(2) == "pc")
				{
					$pc = $cmd->arg(3);
					if ($pc!=-1)
					{
						if (isset($this->welcomes[$bot->dAmn->format($chan)]['pc'][$pc]))
						{
							unset($this->welcomes[$bot->dAmn->format($chan)]['pc'][$pc]);
							$bot->dAmn->say("$cmd->from: The <b>$pc</b> welcome for <b>$chan</b> was deleted.", $cmd->ns);
							$this->save($this->welcomes, "welcomes");
						}
						else
						{
							$bot->dAmn->say("$cmd->from: There is no $pc welcome for $chan.", $cmd->ns);
							$this->save($this->welcomes, "welcomes");
						}
					}
					else $bot->dAmn->say("$cmd->from: You must specify a privclass.", $cmd->ns);
				}
				elseif ($cmd->arg(2) == "indv")
				{
					$person = $cmd->arg(3);
					if ($person != -1)
					{
						if (isset($this->welcomes[$bot->dAmn->format($chan)]['indv'][$person]))
						{
							unset($this->welcomes[$bot->dAmn->format($chan)]['indv'][$person]);
							$bot->dAmn->say("$cmd->from: <b>:dev$person:'s</b> welcome for <b>$chan</b> was deleted.", $cmd->ns);
							$this->save($this->welcomes, "welcomes");
						}
						else
						{
							$bot->dAmn->say("$cmd->from: There is no individual welcome set by $person.", $cmd->ns);
							$this->save($this->welcomes, "welcomes");
						}
					}
					else
					{
						if (isset($this->welcomes[$bot->dAmn->format($chan)]['indv']['all']))
						{
							unset($this->welcomes[$bot->dAmn->format($chan)]['indv']['all']);
							$bot->dAmn->say("$cmd->from: The global individual welcome for <b>$chan</b> was deleted.", $cmd->ns);
							$this->save($this->welcomes, "welcomes");
						}
						else
						{
							$bot->dAmn->say("$cmd->from: There is no global individual welcome for $chan.", $cmd->ns);
							$this->save($this->welcomes, "welcomes");
						}
					}
				}
			}
			elseif ($cmd->arg(1) == "show")
			{
				if ($this->welcomes[$bot->dAmn->format($chan)] != NULL)
				{
					if (isset($this->welcomes[$bot->dAmn->format($chan)]['pc']))
					{
						if($this->welcomes[$bot->dAmn->format($chan)]['pc']==NULL)
							unset($this->welcomes[$bot->dAmn->format($chan)]['pc']);
					}
					
					if (isset($this->welcomes[$bot->dAmn->format($chan)]['indv']))
					{
						if ($this->welcomes[$bot->dAmn->format($chan)]['indv'] == NULL)
							unset($this->welcomes[$bot->dAmn->format($chan)]['indv']);
						else
						{
							$copy = $this->welcomes;
							unset($this->welcomes[$bot->dAmn->format($chan)]['indv']);
						}
					}
					
						
					if ($this->welcomes[$bot->dAmn->format($chan)] != NULL)
					{
						$text = "<b>Settings for $chan</b><br><sub>";
						$text .= "Welcomes for this channel are <b>"
								.$this->switches[$bot->dAmn->format($chan)]
								."</b>.<br>";
					}
							
					foreach ($this->welcomes[$bot->dAmn->format($chan)] as $type => $contents)
					{
						
						if ($type == "pc")
						{
							foreach($contents as $pc => $msg)
							$text .= "<b>$pc</b> - $msg<br>";
						}
						if ($type == "indv")
							$text .= "<b>Global Indv. Message</b> - ".$contents['all']."<br>";
						if ($type == "all")
							$text .= "<b>Global Room Message</b> - $contents<br>";
					}
					
					if ($this->welcomes[$bot->dAmn->format($chan)] == NULL)
					{
						if (is_array($copy))
							$this->welcomes = $copy;
						
						$copy = '';
					}
					//echo $text."\n\n";
					if ($text != NULL)
					{
						$bot->dAmn->say($text, $cmd->ns);
						$this->save($this->welcomes, "welcomes");
					}
					else $bot->dAmn->say("$cmd->from: There are no welcome settings for $chan.", $cmd->ns);
				}
				else $bot->dAmn->say("$from: There are no welcome settings for $chan.", $cmd->ns);
			}
		}
		else
		{
			$chan = $bot->dAmn->deform($cmd->ns);
			if ($cmd->arg(0) == "all")
			{
				$msg = $cmd->arg(1, true);
				$this->welcomes[$bot->dAmn->format($chan)]['all'] = $msg;
				$bot->dAmn->say("$cmd->from: The welcome <i>\"$msg\"</i> was set for all people in <b>$chan</b>.", $cmd->ns);
				if ($this->switches[$bot->dAmn->format($chan)] != "on")
					$this->switches[$bot->dAmn->format($chan)] = "on";
					
				$this->save($this->welcomes, "welcomes");
				$this->save($this->switches, "switch");
			}
			elseif ($cmd->arg(0) == "pc")
			{
				$pc = $cmd->arg(1);
				$msg = $cmd->arg(2, true);
				$this->welcomes[$bot->dAmn->format($chan)]['pc'][$pc] = $msg;
				$bot->dAmn->say("$cmd->from: The welcome <i>\"$msg\"</i> was set for all people in the privclass <b>$pc</b> in <b>$chan</b>.", $cmd->ns);
				if ($this->switches[$bot->dAmn->format($chan)] != "on")
					$this->switches[$bot->dAmn->format($chan)] = "on";
					
				$this->save($this->welcomes, "welcomes");
				$this->save($this->switches, "switch");
			}
			elseif ($cmd->arg(0) == "indv" || $cmd->arg(0) == "individual")
			{
				$this->indv = true;
				if ($cmd->arg(1) == -1)
				{
					$bot->dAmn->say("$cmd->from: People can now set their own welcomes in <b>$chan</b>.", $cmd->ns);
					if ($this->switches[$bot->dAmn->format($chan)] != "on")
						$this->switches[$bot->dAmn->format($chan)] = "on";
						
					$this->save($this->welcomes, "welcomes");
					$this->save($this->switches, "switch");
				}
				else
				{
					$this->welcomes[$bot->dAmn->format($chan)]['indv']['all'] = $cmd->arg(1, true);
					$bot->dAmn->say("$cmd->from: People can now set their own welcomes in <b>$chan</b>, coming after the message <i>\"".$cmd->arg(1, true)."\"</i>.", $cmd->ns);
					if ($this->switches[$bot->dAmn->format($chan)] != "on")
						$this->switches[$bot->dAmn->format($chan)] = "on";
					
					$this->save($this->welcomes, "welcomes");
					$this->save($this->switches, "switch");
				}
			}
			elseif ($cmd->arg(0) == "on" || $cmd->arg(0) == "off")
			{
				if (is_array($this->switches))
				{
					$this->switches[$bot->dAmn->format($chan)] = $cmd->arg(0);
				}
				else
				{
					$this->switches = array($bot->dAmn->format($chan) => $cmd->arg(0));
				}
				$bot->dAmn->say("$cmd->from: Welcomes for $chan have been turned <b>".$cmd->arg(0)."</b>.", $cmd->ns);
				$this->save($this->switches, "switch");
			}
			elseif ($cmd->arg(0) == "show")
			{
				if ($this->welcomes != NULL)
				{
					$count = 0;
					$text = '';
					foreach($this->welcomes as $chan => $welcomes)
					{
						if (isset($this->welcomes[$bot->dAmn->format($chan)]['pc']))
						{
							if($this->welcomes[$bot->dAmn->format($chan)]['pc']==NULL)
								unset($this->welcomes[$bot->dAmn->format($chan)]['pc']);
						}
						
						if (isset($this->welcomes[$bot->dAmn->format($chan)]['indv']))
						{
							if ($this->welcomes[$bot->dAmn->format($chan)]['indv'] == NULL)
								unset($this->welcomes[$bot->dAmn->format($chan)]['indv']);
							else
							{
								$copy = $this->welcomes;
								unset($this->welcomes[$bot->dAmn->format($chan)]['indv']);
							}
						}
						
						
						if ($this->welcomes[$bot->dAmn->format($chan)] == NULL)
						{
							if (is_array($copy))
								$this->welcomes = $copy;
							
							$copy = '';
							continue;
						}
						$chan = $bot->dAmn->deform($chan);
						$text .= "<b>Settings for $chan</b><br><sub>";
						$text .= "Welcomes for this channel are <b>"
							.$this->switches[$bot->dAmn->format($chan)]
							."</b>.<br>";
						foreach ($this->welcomes[$bot->dAmn->format($chan)] as $type => $contents)
						{
							if ($type == "pc")
							{
								foreach($contents as $pc => $msg)
								$text .= "<b>$pc</b> - $msg<br>";
							}
							if ($type == "indv" && isset($contents['all']))
								$text .= "<b>Global Indv. Message</b> - ".$contents['all']."<br>";
							if ($type == "all")
								$text .= "<b>Global Room Message</b> - $contents<br>";
						}
						//echo $text."\n\n";
						$text .= "<br></sub>";
					}
					if ($text != NULL)
					{
						$bot->dAmn->say($text, $cmd->ns);
						$this->save($this->welcomes, "welcomes");
					}
					else $bot->dAmn->say("$cmd->from: There are no welcomes set.", $cmd->ns);
				}
				else $bot->dAmn->say("$cmd->from: There are no welcomes set.", $cmd->ns);
			}
			elseif ($cmd->arg(0) == "del")
			{
				if ($cmd->arg(1) == "all")
				{
					if (isset($this->welcomes[$bot->dAmn->format($chan)]['all']))
					{
						unset($this->welcomes[$bot->dAmn->format($chan)]['all']);
						$bot->dAmn->say("$cmd->from: The global welcome for <b>$chan</b> was deleted.", $cmd->ns);
						$this->save($this->welcomes, "welcomes");
					}
					else
					{
						$bot->dAmn->say("$cmd->from: There is no global welcome for <b>$chan</b>", $cmd->ns);
					}
				}
				elseif ($cmd->arg(1) == "pc")
				{
					$pc = $cmd->arg(2);
					if ($pc !=-1)
					{
						if (isset($this->welcomes[$bot->dAmn->format($chan)]['pc'][$pc]))
						{
							unset($this->welcomes[$bot->dAmn->format($chan)]['pc'][$pc]);
							$bot->dAmn->say("$cmd->from: The <b>$pc</b> welcome for <b>$chan</b> was deleted.", $cmd->ns);
							$this->save($this->welcomes, "welcomes");
						}
						else
						{
							$bot->dAmn->say("$cmd->from: There is no $pc welcome for$chan.", $cmd->ns);
						}
					}
					else $bot->dAmn->say("$cmd->from: You must specify a privclass.", $cmd->ns);
				}
				elseif ($cmd->arg(1) == "indv")
				{
					$person = strtolower($cmd->arg(2));
					if ($person != -1)
					{
						if (isset($this->welcomes[$bot->dAmn->format($chan)]['indv'][$person]))
						{
							unset($this->welcomes[$bot->dAmn->format($chan)]['indv'][$person]);
							$bot->dAmn->say("$cmd->from: <b>:dev$person:'s</b> welcome for <b>$chan</b> was deleted.", $cmd->ns);
							$this->save($this->welcomes, "welcomes");
						}
						else
						{
							$bot->dAmn->say("$cmd->from: There is no individual welcome set by $person.", $cmd->ns);
						}
					}
					else
					{
						if (isset($this->welcomes[$bot->dAmn->format($chan)]['indv']['all']))
						{
							unset($this->welcomes[$bot->dAmn->format($chan)]['indv']['all']);
							$bot->dAmn->say("$cmd->from: The global individual welcome for <b>$chan</b> was deleted.", $cmd->ns);
							$this->save($this->welcomes, "welcomes");
						}
						else
						{
							$bot->dAmn->say("$cmd->from: There is no global individual welcome for $chan.", $cmd->ns);
						}
					}
				}
			}
		}
		if ($cmd->args == '')
		{
			$bot->dAmn->args = "wt";
			$bot->Commands->execute("help");
		}
	}
		
	function c_welcome($cmd, $bot)
	{
		$msg = $cmd->arg(0, true);
		if ($msg != -1)
		{
			if ($this->indv)
			{
				$this->welcomes[$cmd->ns]['indv'][$cmd->from] = $msg;
				$bot->dAmn->say("$cmd->from: Your welcome message is now set to <i>\"$msg\"</i>.", $cmd->ns);
				$this->save($this->welcomes, "welcomes");
			}
			else
			{
				$bot->dAmn->say("$cmd->from: Individual welcomes are not enabled.", $cmd->ns);
			}
		}
		if ($cmd->args == '')
		{
			$bot->dAmn->args = "welcome";
			$bot->Commands->execute("help");
		}
	}
	
	function do_welcome($evt, $bot)
	{
		if (isset($this->welcomes[$evt->ns]) && is_array($this->switches))
		{
			if ($this->switches[$evt->ns] == "on")
			{
				$from = $bot->dAmn->query("members", $evt->ns);
				$from = $from[$evt->from];
				if (!is_array($from)) $from = new Packet($from);
				if (isset($from['pc'])) $pc = $from['pc'];
				if (isset($this->welcomes[$evt->ns]['indv'][strtolower($evt->from)]) && $this->indv)
				{
					$msg = str_replace("{from}", $evt->from, $this->welcomes[$evt->ns]['indv'][strtolower($evt->from)]);
					if (isset($this->welcomes[$evt->ns]['indv']['all']))
						$msg = str_replace("{from}", $evt->from, $this->welcomes[$evt->ns]['indv']['all']) .' '. $msg;
					$bot->dAmn->say($msg, $evt->ns);
				}
				if (isset($this->welcomes[$evt->ns]['pc'][$pc]))
				{
					$msg = str_replace("{from}", $evt->from, $this->welcomes[$evt->ns]['pc'][$pc]);
					$bot->dAmn->say($msg, $evt->ns);
				}
				if (isset($this->welcomes[$evt->ns]['all']))
				{
					$msg = str_replace("{from}", $evt->from, $this->welcomes[$evt->ns]['all']);
					$bot->dAmn->say($msg, $evt->ns);
				}
			}
		}
	}
}
?>
