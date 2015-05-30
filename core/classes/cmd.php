<?php
class cmd extends evt
{
	function __construct($ns, $from, $args, $pkt)
	{
		$this->ns = $ns;
		$this->args = str_replace('""', '', $args);
		$this->from = $from;
		$this->pkt = $pkt;
	}

	function parse_quotes($str, $sep)
	{
		$args = explode('"', str_replace('\"', "<\0>", $str));
		for($num=0;$num<count($args);$num++)
		{
			if ($args[$num] != null)
			{
				if ($args[$num][0] != $sep /*&& $arg[strlen($arg) - 1] != ' '*/)
				{
					if ($args[$num][strlen($args[$num]) - 1] == $sep)
					{
						if (isset($args[$num-1]) && $args[$num-1] == '' && isset($args[$num+1]) && $args[$num+1][0] == ' ')
						{
							$args[$num] = str_replace("<\0>", '"', $args[$num]);
							$args[$num] = str_replace($sep, "\0", $args[$num]);
						}
						elseif (strlen($args[$num+1]) > 0 && $args[$num+1][0] == $sep)
							if ($args[$num+1][0] == $sep)
							{
								$args[$num+1] = str_replace("<\0>", '"', $args[$num+1]);
								$args[$num+1] = str_replace($sep, "\0", $args[$num+1]);
							}
					}
					elseif (isset($args[$num+2]) && strlen($args[$num+2])!=0 && $args[$num+2][strlen($args[$num+2])-1] != '' && $args[$num+2][0] != $sep)
					{
						$args[$num] = str_replace("\0", '"', $args[$num]);
						$args[$num] = str_replace($sep, "\0", $args[$num]);
					}
					else
					{
						if (!isset($args[$num+1]))
						{
							if (isset($args[$num-1]))
							{
								if ($args[$num-1][strlen($args[$num-1])-1] != "\0")
								{
									$args[$num] = '"'.str_replace("<\0>", '\"', $args[$num]);
								}
							}
							else
							{
								$args[$num] = '"'.str_replace("<\0>", '\"', $args[$num]);
							}
						}
						else
						{
							$args[$num] = str_replace("<\0>", '"', $args[$num]);
							$args[$num] = str_replace($sep, "\0", $args[$num]);
						}
					}
				}
				elseif (isset($args[$num-1]) && ($args[$num-1] == '' || $args[$num-1][0] == $sep) && isset($args[$num+1]) && ($args[$num+1] == '' || $args[$num+1][0] == $sep))
				{
					$args[$num] = str_replace("\0", '"', $args[$num]);
					$args[$num] = str_replace($sep, "\0", $args[$num]);
				}
			}
			else
				if (isset($args[$num-1]))
				{
					if ($args[$num-1][strlen($args[$num-1])-1] != $sep)
						if (isset($args[$num-2]) && $args[$num-2] != null)
							if (isset($args[$num-2]) && $args[$num-2][strlen($args[$num-2])-1] != $sep)
							{
								$args[$num] = '"';
							}
				}
			
		}
		$a = array_pop($args);
		
		if ($a != '' && $a != "\0")
			$args[] = $a;

		if ($args[count($args)-1][strlen($args[count($args)-1])-1]==' ')
			$args[count($args)-1] = str_replace(' ', "\0", $args[count($args)-1]);

		$args = join('', $args);
		return $args;
	}
	
	
	function arg($pos, $last=false, $sep=' ')
	{
		$args = $this->args;
		if ($args == '')
		{
			return -1;
		}
		if ($last == true)
		{
			$array = explode($sep, $args);
			if ($pos < count($array))
			{
				$i = $pos;
				$lastarg = '';
				while ($i < count($array))
				{
					$lastarg .= $array[$i].$sep;
					$i++;
				}
				return trim($lastarg);
			}
			else
			{
				return -1;
			}
		}
		else
		{
			if (($i = strpos($args, '"')) !== false && strpos($args, '"', $i + 1) !== false)
			{
				$args = $this->parse_quotes($args, $sep);/*
				if ($args[strlen($args)-1] == "\0" || $args[strlen($args)-1] == " ")
				{
					echo "Got a match for [".str_replace("\0", "<0>", $args[count($args)-1])."]\n";
					$args = substr($args, 0, strlen($args)-1);
					echo "After pop, last arg: [".str_replace("\0", "<0>", $args[count($args)-1])."]\n";
				}
				else
					echo "Last arg is not null? [".str_replace("\0", "<0>", $args[count($args)-1])."]\n";*/
			}
			if (strstr($args, $sep) != false)
			{
				$args = explode($sep, $args);
				if ($pos < count($args))
				{
					$args[$pos] = str_replace("\0", ' ', $args[$pos]);
					return $args[$pos];
				}
				else
					return -1;
			}
			else
			{
				if ($pos > 0)
					return -1;
				else
					return str_replace("\0", " ", $args);
			}
		}
	}
}
?>
