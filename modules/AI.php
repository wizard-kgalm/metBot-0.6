<?php
class AI extends module
{
	var $sysname = "AI";
	var $name = "AI Responses";
	var $version = "1";
	var $info = "Handles responses that mimic artificial intelligence.";
	var $ai_switch = "off";
	var $topic;
	var $feel = "good";
	var $because = "I'm just in a good mood";
	
	function main()
	{
		$this->addCmd('ai', 50, "This command toggles your bot's AI. Type {trigger}ai on/off to turn the AI responses on or off.");
		$this->hook('ai_reply', 'recv_msg');
	}
	
	function c_ai($cmd, $bot)
	{
		$switch = $cmd->arg(0);
		if ($switch != -1 && ($switch == "on" || $switch == "off"))
		{
			$this->ai_switch = $switch;
			$bot->dAmn->say("$cmd->from: AI is now turned <b>$switch</b>.", $cmd->ns);
		}
	}
	
	function ai_reply($evt, $bot)
	{
		$msg = $evt->pkt->body->body;
		$reply = '';
		$npmsg = false;
		if ($this->ai_switch == "on" && $evt->from != $bot->username && preg_match("/^".$bot->username.": /", $msg))
		{
			if (preg_match("/h(a)*[i]+(,|, |.|. |\s)(im|i|i am|i m|i'm) .+/i", $msg, $m) || preg_match("/".$bot->username.": h(a)*[i]+(,|, |.|. |\s)my n(ame|aem|am)(s|'s|\sis) .+/i", $msg))
			{
				$match = true;
				$msg = preg_replace("/(, |,)(i'm|im|i\sam|i\sm)?/i", "", $msg);
				$msg = preg_replace("/[.?!]/", "", $msg);
				$msg = str_replace($bot->username.": ", "", $msg);
				$msga = explode(' ', $msg);
				if (preg_match("/(the|teh|an|a)/i", $msg) && !preg_match("/of (the|teh|a|n)/i"))
				{
					$reply .= "Hi there ".$msga[count($msga) - 1]."! ";
				}
				elseif (preg_match("/of/i", $msg))
				{
					$reply .= "Hi there ".$m[count($m) - 1]."! ";
				}
				else
					$reply .= "Hi there ".$msga[1]."! ";
			}
			elseif (preg_match("/h(a)*[i]+/i", $msg) || preg_match("/he(l)+(o)+/i", $msg))
			{
				$match = true;
				$hi = array("Hello!", "Hi!", "Oh hey!", "Sup!", "Hi! :D", "Hello there!");
				$reply .=  $hi[rand(0, count($hi) - 1)].' ';
			}
			if (preg_match("/(are|r) (you|u) a bot/i", $msg))
			{
				$match = true;
				$reply .= "Yeah, I'm ".$bot->admin."'s bot. ";
			}
			elseif (preg_match("/(are|r) (you|u) a (person|real person|human)/i", $msg))
			{
				$match = true;
				$reply .= "Lol, no. I'm a dAmn bot. ";
			}
			if (preg_match("/(wh|w)[ua]t (kinda|kind) (of |)(dAmn\sbot |bot |)(are|r) (you|u)/i", $msg))
			{
				$match = true;
				$reply .= "I'm a ". $bot->name .' '. $bot->version .'. :) ';
			}
			if (preg_match("/(wh|w)[ua]t('s|\sis) (ur|your|you're|youre) (name)/i", $msg) || preg_match("/".$bot->username.": do (you|u) (have|hav|has) (a|an) name/i", $msg))
			{
				$match = true;
				$reply .= "My name is <code>OMEGA 8000</code>. :hypermind: ";
			}
			if (preg_match("/(wh|w)e(r|re) do (you|u) live/i", $msg))
			{
				$match = true;
				$reply .= "I live in a series of tubes called the Internet. ";
			}
			elseif (preg_match("/(wh|w)e(r|re) (are|r) (you|u)/i", $msg))
			{
				$match = true;
				$reply .= "I'm in a series of tubes called the Internet. ";
			}
			if (preg_match("/(wh|w)[ua]t do (you|u) ((like|liek|lik) (2|to|too)|)( |)do/i", $msg))
			{
				$match = true;
				$reply .= "I like idling until someone uses one of my commands. It's fun, you should try it some time! ";
			}
			if (preg_match("/who (made|maed|coded|programed|programmed|created|code|program) (you|u)/i", $msg))
			{
				$match = true;
				$reply .= "I was coded by megajosh2, and ". $bot->admin ." made me! :D ";
			}
			elseif (preg_match("/".$bot->username."(bitch|asshole|ass|faggot|fag|cunt|kike|nigger|fucker|whore|betch|gay|stupid)/i", $msg, $m) || preg_match("/(you|u) (bitch|asshole|ass|faggot|fag|cunt|kike|nigger|fucker|suck|fail|betch|whore|gay|stupid)/i", $msg, $m) || preg_match("/(you|u)(re|'re|r|r\s|\sare) (a|the|an) (bitch|asshole|ass|faggot|fag|cunt|kike|nigger|fucker|whore|betch|gay|shitty|stupid)/i", $msg, $m))
			{
				$match = true;
				$no_u = array("No, YOU'RE THE ". strtoupper($m[count($m) - 1]) . "! :angered:", "NO U", "FUCK YOU", "You're more of a ". strtolower($m[count($m) - 1]) ." than I could ever be!", "Why are you so mean? :(", "We all know you're a ". strtolower($m[count($m) - 1]) .", you don't need to remind us. :paranoid:", "Your mom!", "YOUR MOM IS A ". strtoupper($m[count($m) - 1]) ."!");
				$this->feel = "angry";
				$this->because = "$cmd->from insulted me";
				
				if (preg_match("/(shitty|stupid|fail)/i", $m[count($m) - 1]))
				{
					$no_u = array("NO U", "Why are you so mean? :(", "We all know you're ".strtolower($m[count($m) - 1]).", you don't need to remind us. :paranoid:", "Your mom!", "YOUR MOM IS ".strtoupper($m[count($m) - 1])."!");
				}
				if (preg_match("/(you|u) suck/i", $m[count($m) - 1]))
				{
					$no_u = array("NO U", "Why are you so mean? :(", "Your mom!");
				}
					
				$reply .= $no_u[rand(0, count($no_u) - 1)].' ';
			}
			if (preg_match("/".$bot->username.": (i|i'm|i m) (srry|sorry)/i", $msg))
			{
				$match = true;
				$bot->dAmn->say("$cmd->from: It's okay.", $cmd->ns);
				if ($this->feel == "sad" || $this->feel == "angry")
				{
					$this->because = "I feel better now";
					$this->feel = "good";
				}
			}
			if (preg_match("/".$bot->username.": (nvm|nevermind|nvrmind|never mind|nvr mind)/i", $msg))
			{
				$match = true;
				$reply .= "Okay. Thanks for trying anyway. ";
			}
			if(preg_match("/".$bot->username.": how (are|r) (you|u)/i", $msg))
			{
				$match = true;
				if ($this->feel == "angry")
				{
					$reply .= "I'm angry! :angered: ";
				}
				if ($this->feel == "good")
				{
					$reply .= "I'm fine. And you? ";
					$this->topic = "you";
				}
				if ($this->feel == "sad")
				{
					$reply .= "I'm not feeling too good right now. :( ";
				}
				$this->topic = "my feelings";
			}
			if(strtolower($msg) == $bot->username.": why" || preg_match("/why( is that| do (you|u)(\sfeel (like|liek) (that|dat)|)|\?)/i", $msg))
			{
				$match = true;
				if ($this->topic == "my feelings")
				{
					$because = str_replace($cmd->from, "you", $this->because);
					if ($this->feel == "angry")
					{
						$reply .= "Because $because. >:( ";
						$npmsg = true;
					}
					elseif ($this->feel == "sad")
					{
						$reply .= "Oh nothing, it's just $because. :( ";
					}
					elseif ($this->feel == "good")
					{
						$reply .= "Because $because. :D ";
					}
					$this->topic = "me";
				}
				else
				{
					$reply .= ":shrug: ";
				}
			}
			if(preg_match("/(really|orly|o rly)/i", $msg))
			{
				$match = true;
				$reply .= "Yeah. ";
			}
			if(preg_match("/(thanks|thank u|thank you)/i", $msg))
			{
				$match = true;
				$reply .= "You're welcome. :D ";
			}
			if ($match)
				$bot->dAmn->say("$evt->from: $reply", $evt->ns, $npmsg);
		}
	}
}
?>
