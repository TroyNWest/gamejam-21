<?php
/**
	Heal the target to full.
*/

namespace Game\ItemActions;

use Game\Item;
use Game\Entity;
use Game\CommunicationService;

class FullHealAction extends ItemAction{
	
	/**
		Called when an item is used on the owner.
	*/
	public function useOnSelf(Item $item, Entity $user) : void{
		$user->setCurrentHP($user->getMaxHP());
		$user->removeItem($item);
		
		CommunicationService::getInstance()->sendMessageToEntityOwner($user, [
			'type' => 'item_remove',
			'id' => $item->getId(),
			'owner_id' => $user->getId()
		]);
	}
	
	/**
		Called when an item is used by the owner on someone else.
	*/
	public function useOnTarget(Item $item, Entity $user, Entity $target) : void{
		$target->setCurrentHP($target->getMaxHP());
		$user->removeItem($item);
		
		CommunicationService::getInstance()->sendMessageToEntityOwner($user, [
			'type' => 'item_remove',
			'id' => $item->getId(),
			'owner_id' => $user->getId()
		]);
	}
	
	/**
		Return a string to describe what this action does.
	*/
	public function describe() : string {
		return 'Heals the target to full';
	}
	
}