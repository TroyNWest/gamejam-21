<?php
/**
	Create Entity Interactions easily.
*/

namespace Game\EntityInteractions;

use Game\Entity;
use Game\EntityInteractions\EntityInteraction;

class EntityInteractionFactory{
	
	protected array $data = [];
	
	private static $instance = null;
	
	public static function getInstance(?string $data_path = null) : EntityInteractionFactory {
		if (self::$instance == null){
			if (!$data_path){
				throw new Exception('First instance call requires a data path');
			}
			
			self::$instance = new EntityInteractionFactory($data_path);
		}
		
		return self::$instance;
	}
	
	private function __construct(string $data_path){
		$this->data = json_decode(file_get_contents($data_path), true);
		
		if ($this->data === NULL){
			throw new Exception('Invalid entity interaction data');
		}
	}

	/**
		Create an Entity.
	*/
	public function createEntityInteraction(string $identifier) : EntityInteraction {
		$data = $this->data[$identifier];
		
		return new $data();
	}
}