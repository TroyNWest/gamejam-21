<?php
/**
	Allow events to be registered for and fired from anything that extends this.
*/

namespace Game;

use Game\Handlers\EventHandler;

class EventEmitter{
	protected array $registry = [];
	
	/**
		Add a handler for a specific type of event to this object instance.
	*/
	public function registerHandler(string $event_type, EventHandler $handler) : void {
		if (!array_key_exists($event_type, $this->registry)){
			$this->registry[ $event_type ] = [];
		}
		
		$this->registry[ $event_type ][] = $handler;
	}
	
	public function unregisterHandler(string $event_type, EventHandler $handler) : void {
		if (!array_key_exists($event_type, $this->registry)){
			return;
		}
		
		$index = array_search($handler, $this->registry[ $event_type ], true);
		if ($index !== false){
			unset($this->registry[ $event_type ][ $index ]);
		}
	}
	
	protected function fireEvent(string $event_type, array $data = []) : void {
		if (!array_key_exists($event_type, $this->registry)){
			return;
		}
		
		foreach ($this->registry[ $event_type ] as $handler){
			$handler->handle($this, $data);
		}
	}
	
	public function triggerEvent(string $event_type, array $data = []) : void {
		$this->fireEvent($event_type, $data);
	}
}