<?php
class Event
{
	var $evt;
	var $evts;
	var $cmd;
	private $bot;
	
	function __construct($bot)
	{
		$this->bot =& $bot;
	}
	
	function recv($type)
	{
		switch ($type)
		{
			case 'recv_msg':
				if (isset($this->evt->body))
				{
					if ($this->evt->cmd == "recv" && $this->evt->body->cmd == "msg")
						return true;
					else return false;
				}
				else return false;
			break;
			case 'recv_join':
				if (isset($this->evt->body))
				{
					if ($this->evt->cmd == "recv" && $this->evt->body->cmd == "join")
						return true;
					else return false;
				}
				else return false;
			break;
			case 'recv_part':
				if (isset($this->evt->body))
				{
					if ($this->evt->cmd == "recv" && $this->evt->body->cmd == "part")
						return true;
					else return false;
				}
				else return false;
			break;
			case 'recv_kick':
				if (isset($this->evt->body))
				{
					if ($this->evt->cmd == "recv" && $this->evt->body->cmd == "kicked")
						return true;
					else return false;
				}
				else return false;
			break;
			case 'property':
				if ($this->evt->cmd=="property")
					return true;
				else return false;
			break;
		}
	}
	
	function process()
	{
		if (is_array($this->evts))
		{
			foreach($this->evts as $event => $command)
			{
				if (!is_array($command))
				{
					if ($this->recv($event) && $this->bot->dAmn->from != $this->bot->username)
					{
						$evt = new evt($this->bot->dAmn->ns, $this->bot->dAmn->from, $this->evt);
						$cmd = explode('::', $command);
						$mod = $cmd[0];
						$func = $cmd[1];
						$this->bot->Modules->mods[$mod]->$func($evt, $this->bot);
					}
				}
				else
				{
					foreach($command as $c)
					if ($this->recv($event) && $this->bot->dAmn->from != $this->bot->username)
					{
						$evt = new evt($this->bot->dAmn->ns, $this->bot->dAmn->from, $this->evt);
						$cmd = explode('::', $c);
						$mod = $cmd[0];
						$func = $cmd[1];
						$this->bot->Modules->mods[$mod]->$func($evt, $this->bot);
					}
				}
			}
		}
	}
}
	
?>
