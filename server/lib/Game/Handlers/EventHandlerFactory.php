<?php
/**
	Helper to create event handlers.
*/

namespace Game\Handlers;

use Game\Handlers\EventHandler;

class EventHandlerFactory{
	
	protected array $data = [];
	
	private static $instance = null;
	
	public static function getInstance(?string $data_path = null) : EventHandlerFactory {
		if (self::$instance == null){
			if (!$data_path){
				throw new Exception('First instance call requires a data path');
			}
			
			self::$instance = new EventHandlerFactory($data_path);
		}
		
		return self::$instance;
	}
	
	private function __construct(string $data_path){
		$this->data = json_decode(file_get_contents($data_path), true);
		
		if ($this->data === NULL){
			throw new Exception('Invalid event handler data');
		}
	}

	/**
		Create a Controller.
	*/
	public function createEventHandler(string $identifier) : EventHandler {
		$data = $this->data[$identifier];
		
		return new $data();
	}
}