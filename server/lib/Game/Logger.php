<?php
/**
	Make things work good.
*/

namespace Game;

class Logger{
	private string $channel = '';
	
	public function __construct($channel){
		$this->channel = $channel;
	}
	
	public function debug($str){
		echo ($this->channel ? "[{$this->channel}] " : '') . $str . "\n";
	}
}