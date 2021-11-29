<?php
/**
	Helper to create item instances based on a common data source.
*/

namespace Game;

use Game\Item;
use Game\ItemActions\ItemActionFactory;

class ItemFactory{
	
	protected array $data = [];
	
	private int $next_id = 1;
	
	private static $instance = null;
	
	public static function getInstance(?string $data_path = null) : ItemFactory {
		if (self::$instance == null){
			if (!$data_path){
				throw new Exception('First instance call requires a data path');
			}
			
			self::$instance = new ItemFactory($data_path);
		}
		
		return self::$instance;
	}
	
	private function __construct(string $data_path){
		$this->data = json_decode(file_get_contents($data_path), true);
		
		if ($this->data === NULL){
			throw new Exception('Invalid item data');
		}
	}

	public function createItem(string $identifier) : Item {
		$data = $this->data[$identifier];
		
		$actions = ItemActionFactory::getInstance()->createItemAction($data['action']);
		
		$item = new Item($this->next_id++, $data['name'], $data['sprite'], $actions);
		
		if ($data['damage']){
			$item->setDamage($data['damage']);
		}
		
		if ($data['armor']){
			$item->setArmor($data['armor']);
		}
		
		if ($data['range']){
			$item->setRange($data['range']);
		}
		
		return $item;
	}
}