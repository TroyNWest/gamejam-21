<?php

namespace Game\Handlers;

use Game\EventEmitter;

class DropLootEventHandler extends EventHandler {
	
	public function handle(EventEmitter $firer, array $data) : void {
		// TODO: Create some loot or container at firer location
		echo "[DropLootEventHandler] fired" . NL;
	}
	
}