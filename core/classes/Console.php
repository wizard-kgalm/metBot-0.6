<?php

class Console
{
	private $bot;
	
	function __construct($bot)
	{
		$this->bot =& $bot;
	}
	
	function msg($text, $type=NULL, $log=true)
	{
		$text = str_replace("\n", "\r\n", $text);
		if ($this->bot->input == true)
		{
			$msg = "\r";
		}
		if ($type == NULL)
		{
			$msg = BLUE_BOLD."[".CYAN.date($this->bot->timestamp).BLUE_BOLD."]".NORM." $text\r\n";
			if ($log==true)
			{
				if ($this->bot->colors)
				{
					$text = $this->strip_colors($text);
				}
				$this->log($text, NULL);
			}
		}
		else
		{
			$msg = BLUE_BOLD."[".CYAN.date($this->bot->timestamp).BLUE_BOLD."][".GREEN_BOLD."$type".BLUE_BOLD."]".NORM." $text\r\n";
			if ($log==true)
			{
				if ($this->bot->colors)
				{
					
					$text = $this->strip_colors($text);
					
				}
				$this->log($text, $type);
			}
		}
		echo $msg;
	}
	
	function log($text, $type)
	{
		is_dir('./data/logs/') || mkdir('./data/logs/');
		$text = trim($text);
		if (isset($type))
		{
			$text = "[".date($this->bot->timestamp)."][$type] $text";
		}
		else
		{
			$text = "[".date($this->bot->timestamp)."] $text";
		}
		$text = $text."\r\n";
		if ($this->bot->log == true)
		{
			if (isset($type))
			{
				is_dir('./data/logs/'.date("M-j-y").'/') || mkdir('./data/logs/'.date("M-j-y").'/');
				$fp = fopen('./data/logs/'.date("M-j-y").'/'.$type.'.txt', 'a');
				fwrite($fp, $text);
				fclose($fp);
			}
		}
	}

	function get($msg, $default = false)
	{
		echo $msg;
		$fp = fopen('php://stdin', 'r');
		$input = trim(fgets($fp));
		while ($input == "" && $default === false)
		{
			echo "Error: value cannot be empty.\r\n";
			echo $msg;
			$fp = fopen('php://stdin', 'r');
			$input = trim(fgets($fp));
		}
		if ($default !== false && $input == "") $input = $default;
		return $input;
	}
	
	function strip_colors($text)
	{
		$text = str_replace(NORM_BOLD, '', $text);
		$text = str_replace(NORM, '', $text);
		
		$text = str_replace(RED_BOLD, '', $text);
		$text = str_replace(RED, '', $text);
		
		$text = str_replace(GREEN_BOLD, '', $text);
		$text = str_replace(GREEN, '', $text);
		
		$text = str_replace(YELLOW_BOLD, '', $text);
		$text = str_replace(YELLOW, '', $text);
		
		$text = str_replace(BLUE_BOLD, '', $text);
		$text = str_replace(BLUE, '', $text);
		
		$text = str_replace(PURPLE_BOLD, '', $text);
		$text = str_replace(PURPLE, '', $text);
		
		$text = str_replace(CYAN_BOLD, '', $text);
		$text = str_replace(CYAN, '', $text);
		
		$text = str_replace(WHITE_BOLD, '', $text);
		$text = str_replace(WHITE, '', $text);
		
		$text = str_replace(BLACK_BOLD, '', $text);
		$text = str_replace(BLACK, '', $text);
		return $text;
	}
	
	function mkprompt()
	{
		$prompt = BLUE_BOLD."[ ".CYAN.$this->bot->admin."@".$this->bot->username." ".GREEN_BOLD.dAmn::deform($this->bot->ns).BLUE_BOLD." ] ".RED_BOLD."$ ".NORM;
		return $prompt;
	}
}

?>
