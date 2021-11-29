<?php
/**
	Container for items.
*/

namespace Game;

use Game\Item;
use Game\CommunicationService;

class Inventory{
	
	protected array $items = [];
	
	protected int $capacity;
	
	/**
		Create a new inventory with the given capacity.
	*/
	public function __construct(int $capacity = 10){
		$this->capacity = $capacity;
	}
	
	/**
		Add an item.
	*/
	public function addItem(Item $item) : bool{
		if (count($this->items) >= $this->capacity){
			return false;
		}
		
		$this->items[] = $item;
		return true;
	}
	
	/**
		Remove an item by instance.
	*/
	public function removeItem(Item $item) : void{
		$index = array_search($item, $this->items, true);
		if ($index !== false){
			unset($this->items[$index]);
		}
	}
	
	/**
		Find an item by Id in this inventory or NULL if not found.
	*/
	public function findItemById(int $id) : ?Item {
		foreach($this->items as $item){
			if ($item->getId() == $id){
				return $item;
			}
		}
		
		return null;
	}
	
	/**
		Find a weapon item if present.
	*/
	public function findWeapon() : ?Item {
		$result = null;
		
		foreach($this->items as $item){
			if ($item->isWeapon()){
				$result = $item;
				break;
			}
		}
		
		return $result;
	}
	
	/**
		Calculate bonus damage.
	*/
	public function getBonusDamage() : int {
		$result = 0;
		foreach ($this->items as $item){
			$result += $item->getDamage();
		}
		
		return $result;
	}
	
	/**
		Calculate bonus armor.
	*/
	public function getBonusArmor() : int {
		$result = 0;
		foreach ($this->items as $item){
			$result += $item->getArmor();
		}
		
		return $result;
	}
	
	/**
		Clear all items.
	*/
	public function clear() : void{
		$this->items = [];
	}
	
	/**
		Serialize data.
	*/
	public function serialize() : array{
		$result = [
			'capacity' => $this->capacity,
			'items' => []
		];
		
		foreach($this->items as $item){
			$data = $item->serialize();
			$result['items'][] = $data;
		}
		
		return $result;
	}
}