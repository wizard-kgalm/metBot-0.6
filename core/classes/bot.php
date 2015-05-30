<?php
class bot
{
	var $name = "metBot";
	var $version = "0.6 (Beta 6)";
	var $startup_time;
	var $username;
	var $password;
	var $admin;
	var $join;
	var $trigger;
	var $input;
	var $say;
	var $exec;
	var $privs;
	var $levels;
	var $away;
	var $notes;
	var $restart;
	var $disconnected = false;
	var $log;
	var $timestamp;
	var $quit = false;
	var $pk;
	var $cookie;
	var $oldpk;
	var $oldcookie;
	var $colors;
	var $ns;
	var $warnings;
	var $dAmn;
	var $Commands;
	var $Modules;
	var $Event;
	var $Console;
	var $override = array();
	
	function __construct()
	{
		$this->dAmn = new dAmn($this);
		$this->Commands = new Commands($this);
		$this->Modules = new Modules($this);
		$this->Event = new Event($this);
		$this->Console = new Console($this);
	}

	function readConfig()
	{
		eval(file_get_contents('./data/config/login.ini'));
		eval($user = @file_get_contents('./data/config/users.ini'));
		$this->username = $username;
		$this->password = $password;
		$this->admin = $admin;
		$this->say = $say;
		$this->exec = $exec;
		$this->trigger = $trigger;
		$this->input = $input;
		$this->join = $join;
		if ($user)
		{
			$this->levels = $levels;
			$this->privs = $privs;
		}
		
		if (!isset($this->privs[100]) || !in_array($this->admin, $this->privs[100]))
			$this->privs[100][] = $this->admin;
		
		if ($this->levels == null)
		{
			$this->levels = 
			array(
				100 => "Owner",
				75  => "Administrator",
				50  => "Operator",
				25  => "Member",
				0   => "Guest",
				-1  => "Banned",
			);
		}
		
		$this->log = $log;
		$this->timestamp = $timestamp;
		$this->startup_time = time();
		$this->oldpk = $token;
		$this->oldcookie = $cookie;
		$this->warnings = $warnings;
		if ($colors == true)
		{
			define('NORM', "\033[0m");
			define('NORM_BOLD', "\033[0m\033[1m");
			define('RED', "\033[0;31m");
			define('RED_BOLD', "\033[1;31m");
			define('GREEN', "\033[0;32m");
			define('GREEN_BOLD', "\033[1;32m");
			define('YELLOW', "\033[0;33m");
			define('YELLOW_BOLD', "\033[1;33m");
			define('BLUE', "\033[0;34m");
			define('BLUE_BOLD', "\033[1;34m");
			define('PURPLE', "\033[0;35m");
			define('PURPLE_BOLD', "\033[1;35m");
			define('CYAN', "\033[0;36m");
			define('CYAN_BOLD', "\033[1;36m");
			define('WHITE', "\033[0;37m");
			define('WHITE_BOLD', "\033[1;37m");
			define('BLACK', "\033[0;30m");
			define('BLACK_BOLD', "\033[1;30m");
		}
		else
		{
			define('NORM', "");
			define('NORM_BOLD', "");
			define('RED', "");
			define('RED_BOLD', "");
			define('GREEN', "");
			define('GREEN_BOLD', "");
			define('YELLOW', "");
			define('YELLOW_BOLD', "");
			define('BLUE', "");
			define('BLUE_BOLD', "");
			define('PURPLE', "");
			define('PURPLE_BOLD', "");
			define('CYAN', "");
			define('CYAN_BOLD', "");
			define('WHITE', "");
			define('WHITE_BOLD', "");
			define('BLACK', "");
			define('BLACK_BOLD', "");
		}
		$this->colors = $colors;
			
	}

	function saveConfig()
	{
		global $dir;
		$contents = 
		"\$username = '".$this->username."';\n\$password = '".$this->password."';\n\$admin = '".$this->admin."';\n\$say = \"".$this->say."\";\n\$exec = \"".$this->exec."\";\n\$".
		"trigger = \"".$this->trigger."\";\n\$input = ".($this->input == true? "true" : "false").";\n\$log = ".($this->log==true?true:false).";\n\$timestamp = '".$this->timestamp."';\n\$token = '".$this->pk."';\n\$cookie = '".$this->cookie."';\n\$colors = ".($this->colors == true ? "true" : "false") .";\n\$warnings = ".($this->warnings == true ? "true" : "false").";";
		$autojoin = "\n\$join = array(";
		foreach($this->join as $j)
		{
			$autojoin .= "\"$j\",\n";
		}
		$autojoin .= ");";
		$contents .= $autojoin;
		$fp = fopen($dir.'./data/config/login.ini', 'w');
		fwrite($fp, $contents);
		fclose($fp);
	}

	function saveUserInfo()
	{
		$contents = "\$privs = ".var_export($this->privs, true).";\n";	
		$contents .= "\$levels = ".var_export($this->levels, true).";";
		$fp = fopen('./data/config/users.ini', 'w');
		fwrite($fp, $contents);
		fclose($fp);
	}
	
	function time($secs)
	{
		$time = array();
		$math['w'] = 3600 * 24 * 7;
		$math['d'] = 3600 * 24;
		$math['h'] = 3600;
		$math['m'] = 60;
		$math['s'] = 1;

		foreach($math as $key => $m)
		{
			$time[$key] = floor($secs / $m);
			$secs = $secs % $m;
		}

		return $time;
	}

	function uptime($time=null)
	{
		if ($time == null)
			$uptime = $this->time(time() - $this->startup_time);
		else $uptime = $this->time(time() - $time);
		$words = array(
				'w' => 'weeks',
				'd' => 'days',
				'h' => 'hours',
				'm' => 'minutes',
				's' => 'seconds'
			);
		$str = NULL;
		foreach($uptime as $key => $u)
		{
			if ($u != 0)
			{
				$str .= $u.' '.$words[$key];
				if ($key != 's') $str .= ' ';
			}
		}
		return $str;
	}

	function config()
	{
		echo "Please enter the following information:\n";
		$this->username = $this->Console->get("Bot username: ");
		$this->password = $this->Console->get("Bot password: ");
		$this->admin = $this->Console->get("Bot administrator (this should be your dA username): ");
		$this->trigger = $this->Console->get("Bot trigger: ");
		$this->say = $this->Console->get("Trigger for \"say\" command (leave empty for >): ", ">");
		$this->exec = $this->Console->get("Trigger for \"exec\" command (leave empty for #): ", "#");
		$this->join = explode(' ', $this->Console->get("Channels to join (seperate with spaces): "));
		$this->timestamp = $this->Console->get("PHP Date Timestamp (leave empty for default or if you don't know): ", "g:i:s a");
		$this->log = strtolower($this->Console->get("Do you want your bot to log chatrooms? [y/n]: ")) == "y" ? true : false;
		$this->warnings = strtolower($this->Console->get("Would you like your bot to warn when someone can't use a command? [y/n]: ")) == "y"?true:false;
		$this->colors = strtolower($this->Console->get("Would you like to use colors in the console window? [y/n]")) == "y" ? true : false;
		$this->saveConfig();
	}
}

?>
