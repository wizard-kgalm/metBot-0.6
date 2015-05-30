<?php
class evt
{
	var $args;
	var $ns;
	var $from;
	var $pkt;

	function __construct($ns, $from, $pkt)
	{
		$this->ns = $ns;
		$this->from = $from;
		$this->pkt = $pkt;
	}
}
?>
