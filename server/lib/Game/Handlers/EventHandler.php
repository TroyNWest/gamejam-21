<?php
/**
	ABC for handling events.
*/

namespace Game\Handlers;

use Game\EventEmitter;

abstract class EventHandler{
	
	abstract public function handle(EventEmitter $firer, array $data) : void;
	
}