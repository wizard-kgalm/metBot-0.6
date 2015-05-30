<?php
class dAmn
{
	var $joined;
	var $args;
	var $from;
	var $ns;
	private $s;
	private $noerror = false;
	private $bot;

	function __construct($bot)
	{
		$this->bot =& $bot;
	}

	function send($data)
	{
		@socket_send($this->s, $data, strlen($data), 0);
	}

	function recv()
	{
		$response = '';
		$return_value = @socket_recv($this->s, $response, 15000, 0);
		if ($this->bot->quit!=true)
		{
			if ($response == NULL && $return_value == FALSE)
			{
				if ($this->bot->input != true)
				{
					//$this->bot->Console->msg("Disconnected bot from dAmn->recv()");
					$this->bot->disconnected = true;
				}
				return;
			}
		}
		return $response;
	}

	function packet_loop() // recieve and process a packet or packets, and return the packet
	{
		$done = false;
		$packet = '';
		while (!$done)
		{
			$pkt = $this->recv();
			$packet .= $pkt;
			$p = explode(chr(0), $packet);
			if (array_pop($p) == '')
			{
				$done = true;
				foreach ($p as $p)
				{
					$this->process($p);
				}
			}
		}
		return $packet;
	}

	function say($msg, $chan, $npmsg=false)
	{
		$chan = $this->format($chan);
		if (substr(trim($msg), 0, 4)=="/me ")
		{
			$this->send("send $chan\n\naction main\n\n". substr($msg, 4) ."\0");
		}
		else
		{
			if ($npmsg)
				$this->send("send $chan\n\nnpmsg main\n\n$msg\0");
			else
				$this->send("send $chan\n\nmsg main\n\n$msg\0");
		}
	}

	function promote($person, $privclass, $chan)
	{
		$chan = $this->format($chan);
		$this->send("send $chan\n\npromote $person\n\n$privclass\0");
	}

	function kick($person, $chan, $reason="")
	{
		$chan = $this->format($chan);
		$this->send("kick $chan\nu=$person\n\n$reason\0");
	}

	function kill($person, $reason="")
	{
	      $this->send("kill login:$person\n\n$reason\0");
	}

	function ban($person, $chan)
	{
		$chan = $this->format($chan);
		$this->send("send $chan\n\nban $person\n\0");
	}

	function unban($person, $chan)
	{
		$chan = $this->format($chan);
		$this->send("send $chan\n\nban $person\n\0");
	}

	function admin($command, $chan)
	{
		$chan = $this->format($chan);
		$this->send("send $chan\n\nadmin\n\n$command\0");
	}

	function get($property, $chan)
	{
		$chan = $this->format($chan);
		$this->send("get $chan\np=$property\n\0");
	}

	function join($chan)
	{
		$chan = $this->format($chan);
		$this->send("join ". $chan . "\n\0");
	}

	function part($chan)
	{
		$chan = $this->format($chan);
		$this->send("part ". $chan . "\n\0");
	}

	function set_title($title, $chan)
	{
		$chan = $this->format($chan);
		$this->send("set $chan\np=title\n\n$title\0");
	}

	function set_topic($topic, $chan)
	{
		$chan = $this->format($chan);
		$this->send("set $chan\np=topic\n\n$topic\0");
	}

	function query($item, $chan) // return data the bot has stored about chatrooms it joined
	{
		$chan = $this->format($chan);
		if (in_array($chan, array_keys($this->info)))
		{
			if (preg_match("/^(topic|title)$/", $item))
			{
				return isset($this->info[$chan][$item]['body'])?$this->info[$chan][$item]['body']:false;
			}
			elseif (preg_match("/^(topic|title)-by$/", $item))
			{
				return isset($this->info[$chan][substr($item, 0, 5)]['by'])?$this->info[$chan][substr($item, 0, 5)]['by']:false;
			}
			elseif (preg_match("/^(topic|title)-ts$/", $item))
			{
				return isset($this->info[$chan][substr($item, 0, 5)]['ts'])?$this->info[$chan][substr($item, 0, 5)]['ts']:false;
			}
			elseif ($item == "members")
			{
				return isset($this->info[$chan]['members'])?$this->info[$chan]['members']:false;
			}
			elseif ($item == "members-list")
			{
				return isset($this->info[$chan]['members'])?array_keys($this->info[$chan]['members']):false;
			}
			elseif ($item == "pc")
			{
				return isset($this->info[$chan]['pc'])?$this->info[$chan]['pc']:false;
			}
			elseif ($item == "login")
			{
				return isset($this->info[$chan])?$this->info[$chan]:false;
			}
			elseif ($item == "info")
			{
				return isset($this->info[$chan]['info'])?$this->info[$chan]['info']:false;
			}
			elseif ($item == "conns")
			{
				return isset($this->info[$chan]['conns'])?$this->info[$chan]['conns']:false;
			}
			elseif (preg_match("/^(pc|user)-info$/", $item))
			{
				return isset($this->info[$chan][str_replace("-", '', $item)])?$this->info[$chan][str_replace("-", '', $item)]:false;
			}
			else return false;
		}
		else return false;
	}


	function parse_tablumps($text)
	{
		$search[]="/&emote\t([^\t])\t([0-9]+)\t([0-9]+)\t(.+)\t(.+)\t/U";
		$replace[]=":\\1:";
		$search[]="/&emote\t(.+)\t([0-9]+)\t([0-9]+)\t(.+)\t(.+)\t/U";
		$replace[]="\\1";
		$search[]="/&br\t/";
		$replace[]="\n";
		$search[]="/&(b|i|s|u|sub|sup|code|ul|ol|li|p|bcode)\t/";
		$replace[]="<\\1>";
		$search[]="/&\\/(b|i|s|u|sub|sup|code|ul|ol|li|p|bcode)\t/";
		$replace[]="</\\1>";
		$search[]="/&acro\t(.*)\t(.*)&\\/acro\t/U";
		$replace[]="<acronym title=\"\\1\">\\2</acronym>";
		$search[]="/&abbr\t(.*)\t(.*)&\\/abbr\t/U";
		$replace[]="<abbr title=\"\\1\">\\2</abbr>";
		$search[]="/&link\t([^\t]*)\t([^\t]*)\t&\t/U";
		$replace[]="\\1 (\\2)";
		$search[]="/&link\t([^\t]*)\t&\t/U";
		$replace[]="\\1";
		$search[]="/&a\t(.*)\t(.*)\t(.*)&\\/a\t/U";
		$replace[]="<a href=\"\\1\" title=\"\\2\">\\3</a>";
		$search[]="/&(iframe|embed)\t(.*)\t([0-9]*)\t([0-9]*)\t&\\/(iframe|embed)\t/U";
		$replace[]="<\\1 src=\"\\2\" width=\"\\3\" height=\"\\4\" />";
		$search[]="/&img\t(.*)\t([0-9]*)\t([0-9]*)\t/U";
		$replace[]="<img src=\"\\1\" width=\"\\2\" height=\"\\3\" />";
		$search[]="/&thumb\t([0-9]*)\t(.*)\t(.*)\t(.*)\t(.*)\t(.*)\t(.*)\t/U";
		$replace[]=":thumb\\1:";
		$search[]="/&dev\t([^\t])\t([^\t]+)\t/U";
		$replace[]=":dev\\2:";
		$search[]="/&avatar\t([^\t]+)\t([^\t]+)\t/U";
		$replace[]=":icon\\1:";
		$search[]="/ width=\"\"/";
		$replace[]="";
		$search[]="/ height=\"\"/";
		$replace[]="";
		$search[]="/&gt;/";
		$replace[]=">";
		$search[]="/&lt;/";
		$replace[]="<";
		$search[]="/&amp;/";
		$replace[]="&";
		$oldtext='';
		while($text!=$oldtext)
		{
			$oldtext=$text;
			$text=preg_replace($search, $replace, $text);
		}

		return $text;
	}

	function process($packet) // process a recieved packet
	{
		$packet = $this->parse_tablumps($packet);
		$packet = new Packet($packet);

		$this->bot->Event->evt = $packet;
		if ($packet->cmd == 'recv')
		{
			$this->ns = $packet->param;
			$packet->param = $this->deform($packet->param);


			if ($packet->body->cmd == "msg")
			{
				$this->bot->Console->msg(NORM_BOLD."<".$packet->body['from']."> ".NORM.$packet->body->body, $packet->param);
				$this->from = $packet->body['from'];
				if (strtolower($packet->body->body) == strtolower($this->bot->username) . ": trigcheck" && $packet->body['from'] != $this->bot->username)
				{
					$this->say("$this->from: My trigger is <code>". $this->bot->trigger, $this->ns);
				}
				if (substr($packet->body->body, 0, strlen($this->bot->say) + 1) == $this->bot->say." " && $packet->body['from'] == $this->bot->admin)
				{
					$packet->body->body = substr_replace($packet->body->body, "", 0, strlen($this->bot->say) + 1);
					$chan = explode(' ', $packet->body->body);
					$chan = $chan[0];
					if ($chan[0] == "#" || $chan[0] == "@")
					{
						$packet->body->body = preg_replace("/^$chan /", "", $packet->body->body);
						$chan = $this->format($chan);
						$this->say($packet->body->body, $chan);
					}
					else $this->say($packet->body->body, $this->ns);
				}
				if (substr($packet->body->body, 0, strlen($this->bot->exec) + 1) == $this->bot->exec." " && $packet->body['from'] == $this->bot->admin)
				{
					$this->bot->Console->msg("Evaluating code...");
					$code = substr_replace($packet->body->body, "", 0, strlen($this->bot->exec) + 1);
					$return = eval('$this->noerror = true; '.$code);
					if ($this->noerror === FALSE)
					{
						$this->say("Error in executed code! Check the console window for more info.", $this->ns);
					}
					elseif ($return !== NULL)
					{
						switch(var_export($return, true))
						{
							case "false":
								$return = "false";
							break;
							case "true":
								$return = "true";
							break;
						}
						$this->say("Code returned:<bcode>". print_r($return, true), $packet->param);
					}
					$this->noerror = false;
				}

				if (substr($packet->body->body, 0, strlen($this->bot->exec) + 2) == $this->bot->exec.": " && $packet->body['from'] == $this->bot->admin)
				{
					$this->bot->Console->msg("Evaluating code...");
					$code = substr_replace($packet->body->body, "", 0, strlen($this->bot->exec) + 1);
					$return = eval('$this->noerror = true; return '.$code);
					if ($this->noerror === FALSE)
					{
						$this->say("Error in executed code! Check the console window for more info.", $this->ns);
					}
					elseif ($return !== NULL)
					{
						switch(var_export($return, true))
						{
							case "false":
								$return = "false";
							break;
							case "true":
								$return = "true";
							break;
						}
						$this->say("Code returned:<bcode>". print_r($return, true), $this->ns);
					}
					$this->noerror = false;
				}

				if (substr($packet->body->body, 0, strlen($this->bot->trigger)) == $this->bot->trigger && $packet->body['from'] != $this->bot->username)
				{
					$body = substr($packet->body->body, strlen($this->bot->trigger));
					if (preg_match("/^[a-zA-Z0-9_]+/", $body))
						$this->bot->Commands->process($packet->body['from'], $packet->body->body);
				}
			}

			if ($packet->body->cmd == "action")
			{
				$this->bot->Console->msg(CYAN_BOLD."* ".$packet->body['from'].CYAN." ".$packet->body->body.NORM, $packet->param);
			}

			if ($packet->body->cmd == "join")
			{
				$this->bot->Console->msg(BLUE_BOLD."** ".$packet->body->param." has joined **".NORM, $packet->param);
				$this->from = $packet->body->param;

				if (!isset($this->info[$this->format($packet->param)]['members'][$this->from]))
					$this->info[$this->format($packet->param)]['members'][$this->from] = $packet->body->body;
				else
				{
					if (!isset($this->info[$this->format($packet->param)]['members'][$this->from]['count']))
						$this->info[$this->format($packet->param)]['members'][$this->from]['count'] = 2;
					else
						++$this->info[$this->format($packet->param)]['members'][$this->from]->args['count'];
				}
			}

			if ($packet->body->cmd == "part")
			{
				if (!isset($packet->body['r']))
				{
					$packet->body['r'] = " ";
				}

				$this->bot->Console->msg(BLUE_BOLD."** ".$packet->body->param." has left [".$packet->body['r']."] **".NORM, $packet->param);
				$this->from = $packet->body->param;
				if (!isset($this->info[$this->ns]['members'][$this->from]['count']))
					unset($this->info[$this->ns]['members'][$this->from]);
				else
					--$this->info[$this->ns]['members'][$this->from]->args['count'];
			}

			if ($packet->body->cmd == "privchg")
			{
				$this->from = $packet->body['by'];
				$this->bot->Console->msg(BLUE_BOLD."** ".NORM.$packet->body->param."'s privclass has been set to ".$packet->body['pc']." by ".$packet->body['by'], $packet->param);
			}

			if ($packet->body->cmd == "kicked")
			{
				$this->from = $packet->body['by'];
				$this->bot->Console->msg(BLUE_BOLD."** ".$packet->body->param." has been kicked by ".$packet->body['by']."** ".NORM.$packet->body->body, $packet->param);

				if (!isset($this->info[$this->ns]['members'][$packet->body->param]['count']))
					unset($this->info[$this->ns]['members'][$packet->body->param]);
				else
					--$this->info[$this->ns]['members'][$packet->body->param]->args['count'];
			}

			if ($packet->body->cmd == "admin")
			{
				if ($packet->body->param == "create" || $packet->body->param == "update")
				{
					$this->bot->Console->msg(BLUE_BOLD."** the privclass ".$packet->body['name']." has been ".$packet->body->param."d by ".$packet->body['by']." with: ".NORM.$packet->body['privs'], $packet->param);
				}

				if ($packet->body->param == "rename" || $packet->body->param == "move")
				{
					$this->bot->Console->msg(BLUE_BOLD."** the privclass ".$packet->body['prev']." has been ".$packet->body->param."d to ".$packet->body['name']." by ".$packet->body['by'].NORM, $packet->param);
				}

				if ($packet->body->param == "show")
				{
					if ($packet->body['p'] == "privclass")
					{
						$info = new Packet($packet->body->body, ' ');
						$this->info[$this->format($packet->param)]['pcinfo'] = $info->args;
					}
					if ($packet->body['p'] == "users")
					{
						$info = new Packet(str_replace(": ", ":", $packet->body->body), ':');
						$this->info[$this->format($packet->param)]['userinfo'] = $info->args;
					}
					$this->bot->Console->msg(GREEN."Got ". $packet->body['p'] ."info for ". $this->deform($packet->param).NORM);
				}
			}
		}

		if ($packet->cmd == 'login')
		{
			$this->bot->Console->msg(GREEN."Login for ". $packet->body['symbol'] ."". $packet->param.": ".$packet['e'].NORM);
		}

		if ($packet->cmd == 'join')
		{
			$packet->param = $this->deform($packet->param);
			$this->bot->Console->msg(GREEN."Join for ".$packet->param.": ".$packet['e'].NORM);
			if ($packet['e'] == "ok")
			{
				$this->joined[] = $this->format($packet->param);
			}
		}

		if ($packet->cmd == 'part')
		{
			$packet->param = $this->deform($packet->param);
			$this->bot->Console->msg(GREEN."Part for ".$packet->param.": ".$packet['e'].NORM);
			if ($packet['e'] == "ok")
			{
				$keys = array_flip($this->joined);
				unset($this->joined[$keys[$this->format($packet->param)]]);
			}
		}

		if ($packet->cmd == 'send')
		{
			$chat = $this->deform($packet->param);
			$this->bot->Console->msg(RED."Error sending to $chat: ".$packet['e'].NORM);
		}

		if ($packet->cmd == 'kick')
		{
			$chat = $this->deform($packet->param);
			$this->bot->Console->msg(RED."Error kicking ".$packet['u']." in $chat: ".$packet['e']);
		}

		if ($packet->cmd == 'disconnect')
		{
			if (!$packet['e']) $packet['e'] = ' ';
			$this->bot->Console->msg(RED_BOLD."** You have been disconnected ** [".$packet['e']."]".NORM);
			$this->bot->disconnected = true;
		}

		if ($packet->cmd == 'ping')
		{
			$this->send("pong\n\0");
			$this->bot->Console->msg(GREEN."ping, pong".NORM);
		}

		if ($packet->cmd == 'kicked')
		{
			$this->bot->Console->msg(RED."** You were kicked by ".$packet['by']."** ".NORM.$packet->body, $this->deform($packet->param));
			$this->join($packet->param);
		}

		if ($packet->cmd == "property")
		{
			$this->info_packet = $packet;
			if ($packet['p'] == "title")
			{
				$this->info[$packet->param]['title']['body'] = str_replace(chr(0), '', $packet->body);
				$this->info[$packet->param]['title']['by'] = $packet['by'];
				$this->info[$packet->param]['title']['ts'] = $packet['ts'];
				$packet->param = $this->deform($packet->param);
				$this->bot->Console->msg(GREEN."Title for ".$packet->param." set by ".$packet['by'].NORM);
			}

			if ($packet['p'] == "topic")
			{
				$this->info[$packet->param]['topic']['body'] = str_replace(chr(0), '', $packet->body);
				$this->info[$packet->param]['topic']['by'] = $packet['by'];
				$this->info[$packet->param]['topic']['ts'] = $packet['ts'];
				$packet->param = $this->deform($packet->param);
				$this->bot->Console->msg(GREEN."Topic for ".$packet->param." set by ".$packet['by'].NORM);
			}
			if ($packet['p'] == "members")
			{
				$members = $packet->body;
				$chat = $packet->param;
				$this->members_packet[$chat] = $members;
				$members = explode("\n\n", $this->members_packet[$chat]);
				foreach($members as $member)
				{
					$member = new Packet($member);

					if (!isset($this->info[$chat]['members'][$member->param]))
						$this->info[$chat]['members'][$member->param] = $member;
					else
					{
						if (!isset($this->info[$chat]['members'][$member->param]['count']))
							$this->info[$chat]['members'][$member->param]['count'] = 2;
						else
							++$this->info[$chat]['members'][$member->param]->args['count'];
					}
				}
				$chat = $this->deform($packet->param);
				$this->bot->Console->msg(GREEN."Got members for $chat".NORM);
			}
			if ($packet['p'] == "privclasses")
			{
				$privs = new Packet($packet->body, ':');
				$this->info[$packet->param]['pc'] = $privs->args;
				$chat = $this->deform($packet->param);
				$this->bot->Console->msg(GREEN."Got privclasses for $chat".NORM);
			}
			if ($packet['p'] == "info")
			{
				$conns = explode("conn\n", $packet->body);
				$info = new Packet(array_shift($conns));
				$info = $info;
				foreach($conns as $key => $conn)
				{
					$conn = new Packet($conn, '=', false);
					$conn->body = str_replace("ns ", '', $conn->body);
					$conn->body = explode("\n\n", $conn->body);
					$conns[$key] = $conn;
				}

				$this->info[$packet->param]['info'] = $info;
				$this->info[$packet->param]['conns'] = $conns;
				$chat = $this->deform($packet->param);
				$this->bot->Console->msg(GREEN."Got whois info on $chat".NORM);
			}
		}

		$this->bot->Event->process();
	}

	//function parse($data, $sep = '=', $addraw=true)

	function getAuthtoken($username, $password) // grab the bot's authtoken
	{
		$socket = @fsockopen ("ssl://www.deviantart.com", 443);
		if ($socket == false)
		{
			$this->bot->Console->msg("Could not open socket to deviantart.com, using last retrieved authtoken...", "");
			$a = array('pk' => $this->bot->oldpk, 'cookie' => $this->bot->oldcookie);
		}
		$POST = "ref=https%3A%2F%2Fwww.deviantart.com%2Fusers%2Floggedin&username=$username&password=$password&reusetoken=1";
		fputs ($socket, "POST /users/login HTTP/1.1\n");
		fputs ($socket, "Host: www.deviantart.com\n");
		fputs ($socket, "User-Agent: metBot\n");
		fputs ($socket, "Accept: text/html\n");
		fputs ($socket, "Cookie: skipintro=1\n");
		fputs ($socket, "Content-Type: application/x-www-form-urlencoded\n");
		fputs ($socket, "Content-Length: " . strlen ($POST) . "\n\n" . $POST);
		$response = "";
		while (!feof ($socket)) $response .= fgets ($socket, 8192);
		fclose ($socket);
		$return = array();
		if(!empty($response))
		{
			$response = urldecode(substr($response, 0, 500));
			$response = substr($response, strpos($response, '=')+1);
			$cookie = substr($response, 0, strpos($response, ';};')+2);
			$array = unserialize($cookie);
			if (is_array($array))
			{
				$token = $array['authtoken'];
				$this->bot->Console->msg(GREEN."Woot, we have the authtoken!".NORM);
				$return['cookie'] = $cookie;
				$return['token'] = $token;
			}
		}
		return $return;
	}

	function login($username, $token) // log in to dAmn
	{
		$this->s = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$connect = @socket_connect($this->s, gethostbyname('chat.deviantart.com'), 3900);
		if ($connect == false)
		{
			$this->bot->Console->msg(GREEN."Failed to connect to dAmn.".NORM);
			$this->bot->disconnected = true;
			return 3;
		}
		elseif ($connect == true)
		{
			$this->bot->Console->msg(GREEN."Connected to dAmn...".NORM);
		}
		$data="dAmnClient 0.3\nagent=metBot\nother=lolgasmdongs\n\0";
		$this->bot->Console->msg(GREEN."Initiating dAmn handshake...".NORM);
		$this->send($data);
		$response = $this->recv();
		$this->process($response);
		$this->bot->Console->msg(GREEN."Logging in...".NORM);
		$data = "login $username\npk=$token\n\0";
		$this->send($data);
		$response = $this->recv();
		$this->process($response);
		$response = new Packet($response);
		switch ($response['e'])
		{
			case 'ok':
				return 0;
			break;
			case 'authentication failed':
				return 1;
			break;
			case 'not privileged':
				return 2;
			break;
			default:
				return 4;
			break;
		}
	}

	function input() // metBot's input feature
	{
		socket_set_nonblock($this->s);
		$this->bot->ns = strlen($this->ns) > 0 ? $this->ns : $this->format($this->bot->join[0]);
		while ($this->bot->input==true)
		{
			$channel = "";
			$this->packet_loop();
			$input_text = $this->bot->Console->get($this->bot->Console->mkprompt(), "");
			if (strpos($input_text, "/quit")==0 && strpos($input_text, "/quit")!==false)
			{
				$this->bot->Console->msg(GREEN."Quitting input...".NORM);
				socket_set_block($this->s);
				$this->bot->input = false;
			}
			elseif (strpos($input_text, "/set")==0 && strpos($input_text, "/set")!==false)
			{
				$this->args = explode(' ', $input_text);
				$channel = $this->args[1];
				$this->bot->ns = $this->format(trim($channel));
				$this->bot->Console->msg(GREEN."Channel is now set to ".BLUE_BOLD."[".GREEN_BOLD.$this->deform($this->bot->ns).BLUE_BOLD."].".NORM);
			}
			elseif (strpos($input_text, "/chan ?")==0 && strpos($input_text, "/chan ?")!==false)
			{
				$this->bot->Console->msg(GREEN."Channel is set to ".BLUE_BOLD."[".GREEN_BOLD.$this->deform($this->bot->ns).BLUE_BOLD."].".NORM);
			}
			elseif(strpos($input_text, "/e")==0 && strpos($input_text, "/e")!==false)
			{
				$this->args = substr($input_text, 2);
				$return_value = eval($this->args);
				if ($return_value != NULL) echo "Code returned:\n\n" . print_r($return_value, true) . "\n";
			}
			elseif(strpos($input_text, "/clear")==0 && strpos($input_text, "/clear")!==false)
			{
				if (PHP_SHLIB_SUFFIX == "dll")
				{
					system('cls');
				}
				elseif (PHP_SHLIB_SUFFIX == "so")
				{
					system('clear');
				}
			}
			elseif(strpos($input_text, "/title")==0 && strpos($input_text, "/title")!==false)
			{
				$channel = substr($input_text, strlen("/title "));
				$c = strlen($channel) > 0 ? $channel : $this->bot->ns;
				$title = $this->query('title', $c);
				$this->bot->Console->msg("Title is\n". $title ."\n\nset by ". $this->query('title-by', $c) ." on ".date("g:i:s a", $this->query('title-ts',$c)) ."\n");
			}
			elseif(strpos($input_text, "/topic")==0 && strpos($input_text, "/topic")!==false)
			{
				$channel = substr($input_text, strlen("/topic "));
				$c = strlen($channel) > 0 ? $channel : $this->bot->ns;
				$topic = $this->query('topic', $c);
				$this->bot->Console->msg("Topic is:\n". $topic ."\n\nset by ". $this->query('topic-by', $c) ." on ".date("g:i:s a", $this->query('topic-ts',$c)) ."\n");
			}
			elseif(strpos($input_text, "/members")==0 && strpos($input_text, "/members")!==false)
			{
				$channel = substr($input_text, strlen("/members "));
				$c = strlen($channel) > 0 ? $channel : $this->bot->ns;
				$members = join(", ", $this->query("members-list", $c));
				$this->bot->Console->msg("Members in ". $this->deform($c) .":\n$members\n");
			}
			elseif (strpos($input_text, $this->bot->trigger)==0 && strpos($input_text, $this->bot->trigger)!==false)
			{
				if (substr($input_text, 0, strlen($this->bot->trigger))==$this->bot->trigger)
				{
					$body = substr($input_text, strlen($this->bot->trigger));
				    if (preg_match("/^[a-zA-Z0-9_]+/", $body))
					{
						$this->ns = $this->bot->ns;
						$this->bot->Commands->process($this->bot->admin, $input_text);
					}
				}
			}
			else
			{
				if (strlen($input_text) > 0)
				{
					$this->bot->Console->msg("Sending message...");
					$this->say($input_text, $this->bot->ns);
				}
				usleep(200000);
			}
		}
	}

	function deform($chat) // makes regular chatrooms turn into #chat, private chats become @user
	{
		if (substr($chat, 0, 5) == "chat:")
			return str_replace("chat:", "#", $chat);
		elseif (substr($chat, 0, 6) == "login:")
			return str_replace("login:", "", $chat);
		elseif (substr($chat, 0, 6) == "pchat:")
		{
			$chat = str_replace("pchat:", "", $chat);
			$chat = explode(':', $chat);
			if ($chat[0] == $this->bot->username)
			{
				return "@$chat[1]";
			}
			else
			{
				return "@$chat[0]";
			}
		}
		else
		{
			if ($chat[0]=="#" || $chat[0]=="@")
				return $chat;
			else
				return "#".$chat;
		}
	}

	function format($chat) // format namespace to be sendable to dAmn
	{
		if (!preg_match("/^(chat|pchat|login):(\s|)/", substr($chat, 0, 6)))
		{
			if ($chat[0] == "#")
			{
				$chat = str_replace("#", "chat:", $chat);
			}
			elseif ($chat[0] == "@")
			{
				$chat = str_replace("@", "", $chat);
				$s = strtolower($chat);
				$me = strtolower($this->bot->username);
				$a = array($s, $me);
				sort($a);
				foreach($a as $key => $name)
				{
					if (preg_match("/$name/i", $chat))
					{
						$a[$key] = $chat;
					}
					else
					{
						$a[$key] = $this->bot->username;
					}
				}
				$chat = "pchat:".join(':', $a);
			}
			else
			{
				$chat = "chat:".$chat;
			}
		}
		return $chat;
	}
}
?>
