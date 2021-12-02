<?php

namespace Game\Handlers;

use Game\EventEmitter;
use Game\EntityFactory;
use Game\CommunicationService;

class DropLootEventHandler extends EventHandler {
	
	/// The entity we should create as a temporary container. This should probably have a TransferInventoryInteraction as well.
	protected string $loot_container_entity_code = 'loot';
	
	/// The distance away from the spawn point we should project the loot
	protected float $distance_from_center = 1.5;
	
	public function handle(EventEmitter $firer, array $data) : void {
		// Walk through the inventory and create a new loot entity for each
		$center = $firer->getPosition();
		
		$items = $firer->getInventory()->getItems();
		$item_count = count($items);
		
		// Figure out how to randomly disperse all items around the source
		$degree_increment = 360 / $item_count;
		$slots = [];
		for ($i = 0; $i < 360; $i += $degree_increment){
			$slots[ $i ] = rand(1, 360); // Give a random weight to each slot
		}
		
		// Then sort the slot numbers, now the slot keys are a randomly ordered array of perfectly separated slots.
		asort($slots);
		$slots = array_keys($slots); // Dump the weights
		
		for ($i = 0; $i < $item_count; $i ++){
			$item = $items[$i];
			
			$degrees = $slots[$i];
			$radians = ($degrees % 360) * M_PI / 180.0;
			
			$x = $center[0];
			$y = $center[1];
			$x += $this->distance_from_center * cos($radians);
			$y += $this->distance_from_center * sin($radians);
			$final = $firer->getMap()->getAdjustedMovePoint($center[0], $center[1], $x, $y);
			
			$container = EntityFactory::getInstance()->createEntity($this->loot_container_entity_code, '', $item->getSpriteCode());
			$container->setPosition($final[0], $final[1]);
			$container->getInventory()->addItem($item);
			$container->setRendererHint('loot');
			$firer->getMap()->addEntity($container);
			
			CommunicationService::getInstance()->broadCast([
				'type' => 'entity_create',
				'data' => $container->serialize()
			]);
		}
		
		// Clear the firer's inventory
		$firer->getInventory()->clear();
	}
}