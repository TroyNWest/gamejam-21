<?php
/**
	Create an item action factory by code.
*/

namespace Game\ItemActions;

use Game\ItemActions\ItemAction;

class ItemActionFactory{
	
	protected array $data = [];
	
	private static $instance = null;
	
	public static function getInstance(?string $data_path = null) : ItemActionFactory {
		if (self::$instance == null){
			if (!$data_path){
				throw new Exception('First instance call requires a data path');
			}
			
			self::$instance = new ItemActionFactory($data_path);
		}
		
		return self::$instance;
	}
	
	private function __construct(string $data_path){
		$this->data = json_decode(file_get_contents($data_path), true);
		
		if ($this->data === NULL){
			throw new Exception('Invalid ItemAction data');
		}
	}
	
	public function createItemAction(string $identifier) : ItemAction {
		$data = $this->data[ $identifier ];
		
		return new $data();
	}
}