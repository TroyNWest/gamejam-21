<?php
/**
	Helper to create factions.
*/

namespace Game\Factions;

use Game\Factions\Faction;

class FactionFactory{
	
	protected array $data = [];
	
	private int $next_id = 1;
	
	private static $instance = null;
	
	public static function getInstance(?string $data_path = null) : FactionFactory {
		if (self::$instance == null){
			if (!$data_path){
				throw new Exception('First instance call requires a data path');
			}
			
			self::$instance = new FactionFactory($data_path);
		}
		
		return self::$instance;
	}
	
	private function __construct(string $data_path){
		$this->data = json_decode(file_get_contents($data_path), true);
		
		if ($this->data === NULL){
			throw new Exception('Invalid faction data');
		}
	}

	public function createFaction(string $identifier) : Faction {
		$data = $this->data[$identifier];
		
		return new $data();
	}
}