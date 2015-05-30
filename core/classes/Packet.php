<?php
class Packet implements ArrayAccess
{	
	var $cmd;
	var $param;
	var $body;
	var $args = array();
	var $raw;
	
	function __construct($data, $sep='=', $parse_body=true, $process=true)
	{
		$this->parse($data, $sep, $process);
		
		if ($parse_body && $this->body != NULL && $this->cmd != 'property' && $this->cmd != 'kicked')
		{
			$this->body = new Packet($this->body, '=', false);
		}
	}
	
	function parse($data, $sep, $process=true) //adapted from photofroggy's PHP parser
	{
		$this->raw = $data;
		if (stristr($data, "\n\n"))
		{
			$this->body = trim(stristr($data, "\n\n"));
			$data = substr($data, 0, strpos($data, "\n\n"));
		}
		
		$data = explode("\n", $data);
		
		foreach($data as $id => $str)
		{
			if (strpos($str, $sep) != 0)
			{
				$this->args[substr($str, 0, strpos($str, $sep))] = substr($str, strpos($str, $sep)+1);
			}
			elseif (strlen($str) >= 1)
			{
				if ($id == 0)
				{
					if (!stristr($str, ' ')) $this->cmd = $str;
					else
					{
						$this->cmd = substr($str, 0, strpos($str, ' '));
						$this->param = trim(stristr($str, ' '));
					}
				}
				else
				{
					$this->args[] = $str;
				}
			}
		}
	}
	
	function offsetExists($offset)
	{
		return isset($this->args[$offset]);
	}
	
	function offsetGet($offset)
	{
		return isset($this->args[$offset]) ? $this->args[$offset] : NULL;
	}
	
	function offsetSet($offset, $value)
	{
		$this->args[$offset] = $value;
	}
	
	function offsetUnset($offset)
	{
		unset($this->args[$offset]);
	}
	
	function __toString()
	{
		return $this->raw;
	}
}
?>
