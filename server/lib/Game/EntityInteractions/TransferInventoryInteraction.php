<?php
/**
	Handle an interaction from another entity.
*/

namespace Game\EntityInteractions;

use Game\EntityInteractions\EntityInteraction;
use Game\Entity;
use Game\CommunicationService;

class TransferInventoryInteraction extends EntityInteraction {
	
	/// How close to us does the interactor need to be to recieve items
	protected float $interact_distance = 4.0;

	public function interact(Entity $me, Entity $interactor) : void {
		if ($interactor->distanceTo($me) > $this->interact_distance){
			return;
		}
		
		$items = $me->getInventory()->getItems();
		$item_count = count($items);
		
		for ($i = 0; $i < $item_count; $i++){
			$item = $items[$i];
			$interactor->getInventory()->addItem($item);
			
			CommunicationService::getInstance()->broadCast([
					'type' => 'item_create',
					'owner_id' => $interactor->getId(),
					'data' => $item->serialize()
			]);
		}
		
		$me->getInventory()->clear();
		
		// Destroy container
		$me->triggerEvent('death');
	}

}