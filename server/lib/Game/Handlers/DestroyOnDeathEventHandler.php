<?php

namespace Game\Handlers;

use Game\EventEmitter;
use Game\CommunicationService;

class DestroyOnDeathEventHandler extends EventHandler {
	
	public function handle(EventEmitter $firer, array $data) : void {
		$firer->getMap()->removeEntity($firer);
		CommunicationService::getInstance()->broadCast([
			'type' => 'entity_destroy',
			'id' => $firer->getId()
		]);
	}
	
}