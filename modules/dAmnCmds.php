<?php
class dAmnCmds extends module
{
	var $sysname = "dAmn";
	var $name = "dAmn Commands";
	var $version = "2";
	var $info = "These are a set of commands relating to dAmn.";
	var $away;
	var $pipe = array();
	
	function main()
	{
		$this->addCmd('away', 0, "This sets an away message for yourself. When people try to talk to you and you have an away message set, the bot replies by telling the person who was trying to talk to you that you are away and tells them your away message. Used <b>{trigger}away <i>message</i></b>.");
		$this->addCmd('back', 0, "If you set an away message, this unsets it so people don't get a notification that you're away anymore. Simply type <b>{trigger}back</b> to remove your away message.");
		$this->addCmd('promote', 75, "Promotes a person to the specified privclass. Used <b>{trigger}promote #<i>channel</i> <i>person</i> <i>privclass</i></b>. The channel parameter is optional.");
		$this->addCmd('join', 50, "Makes your bot join a channel. Used <b>{trigger}join <i>channel</i></b>");
		$this->addCmd('part', 50, "Makes your bot part a channel. Used <b>{trigger}part <i>channel</i></b>. If <i>channel</i> is not set, the bot will part the current channel.");
		$this->addCmd('title', 50, "Sets the title of a chatroom. Used <b>{trigger}title #<i>channel</i> <i>title</i></b>. If a channel is not specified, it will attempt change the title of the current room.");
		$this->addCmd('topic', 50, "Sets the title of a chatroom. Used <b>{trigger}topic #<i>channel</i> <i>topic</i></b>. If a channel is not specified, it will attempt change the topic of the current room.");
		$this->addCmd('joined', 25, "Shows the channels your bot is joined in.");
		$this->addCmd('kick', 50, "Kicks a user in the specified chatroom. Your bot must of course have the privs to do so.<sub><ul><li>Type <b>{trigger}kick <i>user</i> <i>reason</i></b> to kick <i>user</i> with the reason <i>reason</i> in the current chatroom.</li><li>Type <b>{trigger}kick #<i>chat</i> <i>user</i> <i>reason</i></b> to kick <i>user</i> in #<i>chat</i> for reason.</li></ul>");
		$this->addCmd('ban', 75, "Bans a user in the specified chatroom. Your bot must of course have he privileges to do so.<sub><ul><li>Type {trigger}ban <i>user</i> to ban <i>user</i> in the current chatroom.</li>Type {trigger}ban #<i>chat</i> <i>user</i> to ban <i>user</i> in the channel #<i>chat</i>.</li></ul>");
		$this->addCmd('unban', 75, "Unbans a user in the specified chatroom. Your bot must of course have he privileges to do so.<sub><ul><li>Type <b>{trigger}unban <i>user</i></b> to unban <i>user</i> in the current chatroom.</li>Type <b>{trigger}unban #<i>chat</i> <i>user</i></b> to unban <i>user</i> in the channel #<i>chat</i>.</li></ul>");
		$this->addCmd('kban', 75, "Kicks and bans a user in the specified chatroom. This allows you to give a reason for banning someone by kicking them with a reason.<sub><ul><li>Type <b>{trigger}kban <i>user</i> <i>reason</i></b> to kick <i>user</i> with the reason <i>reason</i> in the current chatroom, then ban them immediately.</li><li>Type <b>{trigger}kick #<i>chat</i> <i>user</i> <i>reason</i></b> to kick <i>user</i> in #<i>chat</i> for reason</li>, then ban them immediately.</ul>");
		$this->addCmd('pipe', 25, "Send what happens in one channel into another channel. <ul><li><b>{trigger}pipe <i>channel1 channel2</i></b> to send what happens in <i>channel1</i> to <i>channel2</i></li><li>Use {trigger}pipe <i>person channel1 channel2</i> to send what <i>person</i> does in <i>channel1</i> to <i>channel2</i></li><li>Use {trigger}pipe stop <i>channel1</i> <i>channel2</i> to stop piping <i>channel1</i> to <i>channel2</i>.</li></ul>");
		$this->hook('pipe', 'recv_msg');
		$this->hook('pipe', 'recv_join');
		$this->hook('pipe', 'recv_part');
		$this->hook('pipe', 'recv_kick');
		$this->hook('away', 'recv_msg');

		$this->addCmd('whois', 0, "Whois people with the bot. Used {trigger}whois <i>person</i>");
		$this->addCmd('admin', 75, "Do an admin command in a channel. Used {trigger}admin #<i>channel</i> <i>command</i>. The channel is optional.<ul><li>Ex: {trigger}admin #MyRoom update privclass Guests +smilies");
		$this->addCmd('get', 25, "Get parameters for the specified chatroom. The #<i>channel</i> parameter is optional, and if not specified, the command is performed on the current channel.<sub><ul><li>Type <b>{trigger}get #<i>channel</i> title/topic</b> to get the title or topic for the specified channel.</li><li>Type <b>{trigger}get #<i>channel</i> members</b> to get a list of people in the specified channel.</li></ul>");
		$this->addCmd('ping', 0, "Shows how long it took to recieve \"Ping?\" in seconds. Used to test connection speed.");
	}
	
	function away($evt, $bot)
	{
		$to = explode(":", $evt->pkt->body->body);
		if (isset($this->away))
		{
			if (in_array($to[0], array_keys($this->away)) && $evt->pkt->body['from'] != $bot->username)
			{
				$bot->dAmn->say("$evt->from: {$to[0]} is away [<i>{$this->away[$to[0]]}</i>]", $evt->ns);
			}
		}
	}

	function c_away($cmd, $bot)
	{
		if (strlen($cmd->args) <= 0)
		{
			$cmd->args = "no message";
		}
		$bot->dAmn->say("$cmd->from: Your away message is now set to [<i>$cmd->args</i>]", $cmd->ns);
		$this->away[$cmd->from] = $cmd->args;
	}

	function c_admin($cmd, $bot)
	{
		$ns = $cmd->arg(0);
		if ($ns != -1)
		{
			if ($ns[0] == "#" || $ns[0] == "@")
			{
				$bot->dAmn->args = $cmd->arg(1, true);
				$bot->dAmn->admin($bot->dAmn->args, $ns);
			}
			else
			{
				$bot->dAmn->args = $cmd->args;
				$bot->dAmn->admin($bot->dAmn->args, $cmd->ns);
			}
			if (preg_match("/^show (privclass|users)/", $bot->dAmn->args, $m))
			{
				$bot->dAmn->packet_loop();
				$chan = ($ns[0]=="#"||$ns[0]=="@")? $ns : $cmd->ns;
				$info = array();
				if ($m[1] == "privclass")
				{
					$info = $bot->dAmn->query("pc-info", $chan);
				}
				elseif ($m[1] == "users")
				{
					$info = $bot->dAmn->query("user-info", $chan);
				}

				$txt = '';
				foreach($info as $key => $val)
				{
					if ($val != NULL)
					{
						if ($m[1]=="users")
						{
							$val = explode(' ', $val);
							$a = array();
							foreach($val as $v)
							{
								$v = $v[0].'<b></b>'.$v[1].'<b></b>'.substr($v, 2);
								$a[] = $v;
							}							
							$val = join(' ', $a);
						}
						$txt[]="<b>$key</b>: $val";
					}
					else
						$txt[]="<b>$key:</b> no members";
				}
				$txt = join("\n", $txt);
				$bot->dAmn->say("<b>".ucfirst($m[1])." info for ".$bot->dAmn->deform($chan)."</b><br><sub>$txt", $cmd->ns);
			}
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You must specify a command to perform.", $cmd->ns);
		}
	}	

	function c_back($cmd, $bot)
	{
		if (isset($this->away[$cmd->from]))
		{
			unset($this->away[$cmd->from]);
			$bot->dAmn->say("$cmd->from: Your away message has now been unset.", $cmd->ns);
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You have not set an away message.", $cmd->ns);

		}
	}

	function c_promote($cmd, $bot)
	{
		$person = $cmd->arg(0);
		$privclass = $cmd->arg(1);
		if ($person[0] == "#" || $person[0] == "@")
		{
			$chat = $cmd->arg(0);
			$person = $cmd->arg(1);
			$privclass = $cmd->arg(2);
		}

		if ($person != -1)
		{
			if ($privclass == -1) $privclass = '';
			if (isset($chat) && $chat != -1)
			{
				$bot->dAmn->promote($person, $privclass, $chat);
			}
			else
			{
				$bot->dAmn->promote($person, $privclass, $cmd->ns);
			}
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You need to set a person and a privclass.", $cmd->ns);
		}
	}

	function c_join($cmd, $bot)
	{
		$chat = $cmd->arg(0);
		if ($chat != -1)
		{
			$bot->dAmn->join($chat);
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You did not set a channel to join", $cmd->ns);
		}
	}

	function c_part($cmd, $bot)
	{
		$chat = $cmd->arg(0);
		if ($chat != -1)
		{
			$bot->dAmn->part($chat);
		}
		else
		{
			$bot->dAmn->part($cmd->ns);
		}
	}

	function c_title($cmd, $bot)
	{
		$chat = $cmd->arg(0);
		$title = $cmd->arg(1, true);

		if ($title != -1 && $chat != -1)
		{
			if ($chat[0] == "#")
			{
				$bot->dAmn->set_title($title, $chat);
			}
			else
			{
				$title = $chat.' '.$title;
				$bot->dAmn->set_title($title, $cmd->ns);
			}
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You did not set a title.", $cmd->ns);
		}
	}

	function c_topic($cmd, $bot)
	{
		$chat = $cmd->arg(0);
		$topic = $cmd->arg(1, true);

		if ($topic != -1 && $chat != -1)
		{
			if ($chat[0] == "#")
			{
				$bot->dAmn->set_topic($topic, $chat);
			}
			else
			{
				$topic = $chat.' '.$topic;
				$bot->dAmn->set_topic($topic, $cmd->ns);
			}
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You did not set a topic.", $cmd->ns);
		}
	}
	
	function c_kick($cmd, $bot)
	{
		$person = $cmd->arg(0);
		if (preg_match('/^#/', $cmd->arg(1)))
		{
			$chat = $cmd->arg(1);
			$reason = $cmd->arg(2, true);
		}
		else
			$reason = $cmd->arg(1, true);
		
		if ($person != -1)
		{
			if (isset($chat))
			{
				if ($reason != -1)
					$bot->dAmn->kick($person, $chat, $reason);
				else
					$bot->dAmn->kick($person, $chat);
			}
			else
			{
				if ($reason != -1)
					$bot->dAmn->kick($person, $cmd->ns, $reason);
				else
					$bot->dAmn->kick($person, $cmd->ns);
			}
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You must name a user to kick.", $cmd->ns);
		}
	}
	
	function c_ban($cmd, $bot)
	{
		$person = $cmd->arg(0);
		$chat = $cmd->arg(1);
		
		if ($person != -1)
		{
			if ($chat != -1)
				$bot->dAmn->ban($person, $chat);
			else
				$bot->dAmn->ban($person, $cmd->ns);
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You must name a user to ban.", $cmd->ns);
		}
	}

	function c_unban($cmd, $bot)
	{
		$person = $cmd->arg(0);
		$chat = $cmd->arg(1);
		
		if ($person != -1)
		{
			if ($chat != -1)
				$bot->dAmn->unban($person, $chat);
			else
				$bot->dAmn->unban($person, $cmd->ns);
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You must name a user to unban.", $cmd->ns);
		}
	}
	
	function c_kban($cmd, $bot)
	{
		$person = $cmd->arg(0);
		if (preg_match('/^#/', $cmd->arg(1)))
		{
			$chat = $cmd->arg(1);
			$reason = $cmd->arg(2, true);
		}
		else
			$reason = $cmd->arg(1, true);
		
		if ($person != -1)
		{
			if (isset($chat))
			{
				if ($reason != -1)
				{
					$bot->dAmn->kick($person, $chat, $reason);
					$bot->dAmn->ban($person, $chat);
				}
				else
				{
					$bot->dAmn->kick($person, $chat);
					$bot->dAmn->ban($person, $chat);
				}
			}
			else
			{
				if ($reason != -1)
				{
					$bot->dAmn->kick($person, $cmd->ns, $reason);
					$bot->dAmn->ban($person, $cmd->ns);
				}
				else
				{
					$bot->dAmn->kick($person, $cmd->ns);
					$bot->dAmn->ban($person, $cmd->ns);
				}
			}
		}
		else
		{
			$bot->dAmn->say("$cmd->from: You must name a user to kick and ban.", $cmd->ns);
		}
	}
		

	function c_joined($cmd, $bot)
	{
		$joined = '';
		foreach ($bot->dAmn->joined as $key => $j)
		{
			if ($key != count($bot->dAmn->joined) - 1)
			{
				$joined[] = $bot->dAmn->deform($j);
			}
			else
			{
				$joined[] = $bot->dAmn->deform($j);
			}
		}
		$bot->dAmn->say("<abbr title=\"$cmd->from\"></abbr><b>Joined channels:</b> ".join(', ', $joined), $cmd->ns);
	}
	
	function c_pipe($cmd, $bot)
	{
		if ($cmd->arg(2) != -1)
		{
			$person = $cmd->arg(0);
			$chan1 = $cmd->arg(1);
			$chan2 = $cmd->arg(2);
		}
		else
		{
			$chan1 = $cmd->arg(0);
			$chan2 = $cmd->arg(1);
		}
		
		if (isset($person))
		{
			if ($person != "stop")
			{
				$this->pipe[] = "$person\0".$bot->dAmn->format($chan1)."\0".$bot->dAmn->format($chan2);
				$chan1 = $bot->dAmn->deform($chan1);
				$chan2 = $bot->dAmn->deform($chan2);
				$bot->dAmn->say("$cmd->from: Messages received from :dev$person: in <b>$chan1</b> will be piped to <b>$chan2</b>.", $cmd->ns);
			}
			else
			{
				$found = false;
				foreach($this->pipe as $key => $pipe)
				{
					if (preg_match("/".$bot->dAmn->format($chan1)."\\0".$bot->dAmn->format($chan2)."$/", $pipe))
					{
						unset($this->pipe[$key]);
						$found = true;
						$chan1 = $bot->dAmn->deform($chan1);
						$chan2 = $bot->dAmn->deform($chan2);
						break;
					}
				}
				if ($found)
				{
					$pipe = explode("\0", $pipe);
					if (count($pipe) == 2)
						$bot->dAmn->say("$cmd->from: Piping from <b>$chan1</b> to <b>$chan2</b> has stopped.", $cmd->ns);
					else $bot->dAmn->say("$cmd->from: Piping of :dev$pipe[0]: from <b>$chan1</b> to <b>$chan2</b> has stopped.", $cmd->ns);
				}
				else
				{
					$bot->dAmn->say("$cmd->from: ".$bot->dAmn->deform($chan1)." isn't being piped.", $cmd->ns);
				}
			}
		}
		else
		{
			if ($chan1!=-1)
			{
				if ($chan2!=-1)
				{
					$this->pipe[] = $bot->dAmn->format($chan1)."\0".$bot->dAmn->format($chan2);
					$chan1 = $bot->dAmn->deform($chan1);
					$chan2 = $bot->dAmn->deform($chan2);
					$bot->dAmn->say("$cmd->from: Messages received from <b>$chan1</b> will be piped to <b>$chan2</b>.", $cmd->ns);
				}
			}
		}
	}
	
	function pipe($evt, $bot)
	{
		$pipes = $this->pipe;
		foreach($pipes as $key => $pipe)
		{
			if(preg_match("/^$evt->ns\\0/", $pipe))
			{
				$evt->from = $evt->from[0].'<b></b>'.substr($evt->from, 1);
				$pipe = explode("\0", $pipe);
				if ($evt->pkt->cmd == "recv")
				{
					if ($evt->pkt->body->cmd == "msg")
						$bot->dAmn->say("<b>[".$bot->dAmn->deform($evt->ns)."]<$evt->from></b> ".$evt->pkt->body->body, $pipe[1]);
					if ($evt->pkt->body->cmd == "join")
						$bot->dAmn->say("<b>[".$bot->dAmn->deform($evt->ns)."]** $evt->from</b> has joined", $pipe[1]);
					if ($evt->pkt->body->cmd == "part")
						$bot->dAmn->say("<b>[".$bot->dAmn->deform($evt->ns)."]** $evt->from</b> has left", $pipe[1]);
					if ($evt->pkt->body->cmd == "kicked")
					{
						if ($evt->pkt->body->body != "")
							$msg = " * ".$evt->pkt->body->body;
						$bot->dAmn->say("<b>[".$bot->dAmn->deform($evt->ns)."]**</b> ".$evt->pkt->body->param." has been kicked by $evt->from$msg", $pipe[1]);
					}
				}
			}
			elseif (@preg_match("/^$evt->from\\0$evt->ns\\0/", $pipe))
			{
				$pipe = explode("\0", $pipe);
				$evt->from = $evt->from[0].'<b></b>'.substr($evt->from, 1);
				if ($evt->pkt->cmd == "recv" && $evt->pkt->body->cmd == "msg")
					$bot->dAmn->say("<b>[".$bot->dAmn->deform($evt->ns)."]<".$evt->from."></b> ".$evt->pkt->body->body, $pipe[2]);
			}
		}
	}
	
	function c_whois($cmd, $bot)
	{
		$person = $cmd->arg(0);
		$bot->dAmn->get("info", "login:$person");
		$bot->dAmn->packet_loop();
		$login = $bot->dAmn->query("login", "login:$person");
		if (!$login)
		{
			$bot->dAmn->say("$cmd->from: This person isn't on dAmn right now.", $cmd->ns);
		}
		else
		{
			$msg = "<sub><b>:dev$person:</b><br></sub>".
			       "&nbsp;:icon$person:<br>".
			       "<li>".$login['info']['realname']."</li>".
			       "<li>".$login['info']['typename']."</li>".
			       "</ul>";
			
			foreach($login['conns'] as $num => $conn)
			{
				$num += 1;
				$msg .= "<b><u>connection $num:</u></b><br>";
				$online = $bot->uptime(time() - $conn['online']);
				$date = date("D M j, y [".$bot->timestamp." T]", time() - $conn['online']);
				$msg .= "<b>online for:</b> <abbr title=\"$date\">$online</abbr><br>";
				$idle = $bot->uptime(time() - $conn['idle']);
				if ($idle == '')
					$idle = "0 seconds";
					
				$msg .= "<b>idle:</b> $idle<br>".
					"<b>chatrooms:</b> ";
				$chats = array();
				foreach($conn->body as $chat)
				{
					$chats[] = $bot->dAmn->deform($chat);
				}
				$msg .= join(" ", $chats);
				$msg .= "<br><br>";
			}
			$bot->dAmn->say($msg, $cmd->ns);
		}		       
	}
	
	function c_get($cmd, $bot)
	{
		$type = $cmd->arg(0);
		if ($type[0] == "#" || $type[0] == "@")
		{
			$ns = $type;
			$type = $cmd->arg(1);
		}
		else
		{
			$ns = $cmd->ns;
		}
		switch($type)
		{
			case 'title':
			case 'topic':
				$body = str_replace("\n", "<br>", $bot->dAmn->query($type, $ns));
				$by = $bot->dAmn->query("$type-by", $ns);
				$by = $by[0].'<b></b>'.substr($by, 1);
				$ts = $bot->dAmn->query("$type-ts", $ns);
				if ($body == NULL)
				{
					$bot->dAmn->say("$cmd->from: Could not retrieve the $type.", $cmd->ns);
				}
				else
				{
					$bot->dAmn->say("<abbr title=\"$cmd->from\"></abbr>".ucfirst($type)." for ".$bot->dAmn->deform($ns)." is:<br>$body<br><sub>set by <b>$by</b> on <b>".date('F j, Y g:i:s a T', $ts)."</b>", $cmd->ns);
				}
			break;
			case 'members':
				$pc = $bot->dAmn->query("pc", $ns);
				$members = $bot->dAmn->query("members", $ns);
				if (!$members)
				{
					$bot->dAmn->say("$cmd->from: Could not retrieve members list.", $cmd->ns);
					return;
				}
				$c = $bot->dAmn->deform($ns);
				if ($c[0] == "#")
				{
					$num = array();
					foreach($pc as $p)
					{
						$num[$p] = 0;
						foreach($members as $mem)
						{
							if ($mem['pc'] == $p)
							{
								++$num[$p];
							}
						}
					}
					$txt = '';
					foreach($pc as $p)
					{
						if ($num[$p] == 0) continue;
						$txt .= "<b>$p</b>:<br>";
						$a = array();
						foreach($members as $name => $mem)
						{
							if ($mem['pc'] == $p)
							{
								$a[] = $mem['symbol']."<b></b>".$name[0]."<b></b>".substr($name, 1).
								       (isset($mem['count'])? "[".$mem['count']."]" : '');
							}
						}
						$txt .= join(', ', $a)."<br>";
					}
				}
				elseif($c[0] == "@")
				{
					$a = array();
					foreach($members as $name => $mem)
					{
						$a[] = $mem['symbol']."<b></b>".$name[0]."<b></b>".substr($name, 1).
						       (isset($mem['count'])? "[".$mem['count']."]" : '');
					}
					$txt .= join('<br>', $a);
				}
				$bot->dAmn->say("<b>Members in ".$bot->dAmn->deform($ns)."</b><br><sub>$txt</sub>", $cmd->ns);
			break;
			default:
				$bot->dAmn->args = "get";
				$bot->Commands->execute("help");
		}
	}
	
	function c_ping($cmd, $bot)
	{
		$t = microtime(true);
		$bot->dAmn->say("Ping?", $cmd->ns);
		$bot->dAmn->packet_loop();
		$passed = round(microtime(true) - $t, 4);
		$bot->dAmn->say("Pong! $passed s", $cmd->ns);
	}
}
?>
