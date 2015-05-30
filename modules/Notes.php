<?php
class Notes extends module
{
	var $sysname = "Notes";
	var $name = "Notes System";
	var $version = "1";
	var $info = "This module allows you to send and store notes with your bot.";
	var $notes;
	var $notified;
	
	function main()
	{
		$this->addCmd('note', 0, "This command allows you to read and send notes with the bot. <sub><ul><li><b>{trigger}note list</b> show's your note inbox.</li><li><b>{trigger}note read <i>id</i></b> Reads note number #<i>id</i>. Ex. <code>{trigger}note read 0</code></li><li><b>{trigger}note clear</b> clears your notes.</li><li><b>{trigger}note <i>person</i> <i>message</i></b> sends a note to <i>person</i> with the message <i>message</i>.</li></ul></sub>");
		$this->hook('notify', 'recv_msg');
		$this->hook('notify', 'recv_join');
		$this->load($this->notes, "notes");
	}
	function c_note($cmd, $bot)
	{
		$command = $cmd->arg(0);
		$message = $cmd->arg(1, true);

		if ($command == "read")
		{
			if ($message[0] == "#")
			{
				$message = substr($message, 1, strlen($message));
			}
	
			if (isset($this->notes[$cmd->from][$message]))
			{
				$time = $this->notes[$cmd->from][$message]['ts'];
				$sender = $this->notes[$cmd->from][$message]['from'];
				$msg = $this->notes[$cmd->from][$message]['msg'];
				$say = "&#171; Note <b>#$message</b> &#187;<br><sub>&#171; To <b>$cmd->from</b> from <b>$sender</b> &#187;<br>&#171; <b>Sent:</b> ". date('F j, Y g:i:s a T', $time) ." &#187;</sub><br>$msg<br><br><sub>Type ". $bot->trigger ."note clear to clear your notes.";
				$bot->dAmn->say($say, $cmd->ns);
			}
			else
			{
				$bot->dAmn->say("$cmd->from: You have no note with the id #$message.", $cmd->ns);
			}
		}

		elseif ($command == "clear")
		{
			if (isset($this->notes[$cmd->from]))
			{
				if ($message != -1)
				{
					if (isset($this->notes[$cmd->from][$message]))
					{
						unset($this->notes[$cmd->from][$message]);
						$i = 0;
						foreach($this->notes[$cmd->from] as $note)
						{
							$notes[$cmd->from][$i] = $note;
							$i++;
						}
						$this->notes = $notes;
						$bot->dAmn->say("$cmd->from: Note #$message has been deleted.", $cmd->ns);
						$this->save($this->notes, "notes");
					}
					else
					{
						$bot->dAmn->say("You have no note with the id #$message.", $cmd->ns);
					}
				}
				else
				{
					unset($this->notes[$cmd->from]);
					$bot->dAmn->say("$cmd->from: Your notes have been cleared.", $cmd->ns);
					$this->save($this->notes, "notes");
				}
			}
		}

		elseif ($command == "list")
		{
			if (isset($this->notes[$cmd->from]))
			{
				$notes = NULL;
				$i = count($this->notes[$cmd->from]);
				$n = 0;
				while($n < $i)
				{
					$notes .= "#$n<br>";
					$n++;
				}
				$bot->dAmn->say("<abbr title=\"$cmd->from\"></abbr><sub><b><u>Notes</u></b></sub><br>$notes<br><br>Type ". $bot->trigger ."note read [id] to read note #<i>id</i>. Example: ". $bot->trigger ."note read 0", $cmd->ns);
			}
			else
			{
				$bot->dAmn->say("$cmd->from: You have no notes.", $cmd->ns);
			}
		}

		else
		{
			if ($command != -1 && $message != -1)
			{
				if (isset($this->notes[$command])) $i = count($this->notes[$command]);
				else
				{
					$i = 0;
				}
				$this->notes[$command][$i]['msg'] = $message;
				$this->notes[$command][$i]['ts'] = time();
				$this->notes[$command][$i]['from'] = $cmd->from;
				if (isset($this->notified[$command])) unset($this->notified[$command]);
				$bot->dAmn->say("$cmd->from: Your note has been sent to $command.", $cmd->ns);
				$this->save($this->notes, "notes");
			}
			else
			{
				
				$bot->dAmn->args = "note";
				$bot->Commands->execute('help');
			}
		}
	}

	function notify($evt, $bot)
	{
		if (isset($this->notes[$evt->from]) && !isset($this->notified[$evt->from]))
		{
			$i = count($this->notes[$evt->from]);
			if ($i != 1)
			{
				$bot->dAmn->say("$evt->from: You have $i notes. Type ". $bot->trigger ."note list to see what notes you have.", $evt->ns);
			}
			else
			{
				$bot->dAmn->say("$evt->from: You have $i note. Type ". $bot->trigger ."note list to see what notes you have.", $evt->ns);
			}
			$this->notified[$evt->from] = true;
		}
	}
}
?>
