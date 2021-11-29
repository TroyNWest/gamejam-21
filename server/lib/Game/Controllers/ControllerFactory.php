<?php
/**
	Helper to create entities.
*/

namespace Game\Controllers;

use Game\Controllers\Controller;

class ControllerFactory{
	
	protected array $data = [];
	
	private static $instance = null;
	
	public static function getInstance(?string $data_path = null) : ControllerFactory {
		if (self::$instance == null){
			if (!$data_path){
				throw new Exception('First instance call requires a data path');
			}
			
			self::$instance = new ControllerFactory($data_path);
		}
		
		return self::$instance;
	}
	
	private function __construct(string $data_path){
		$this->data = json_decode(file_get_contents($data_path), true);
		
		if ($this->data === NULL){
			throw new Exception('Invalid controller data');
		}
	}

	/**
		Create a Controller.
	*/
	public function createController(string $identifier, string $player_id = '') : Controller {
		$data = $this->data[$identifier];
		
		echo "[ControllerFactory] Trying to make {$data}\n";
		
		return new $data($player_id);
	}
}