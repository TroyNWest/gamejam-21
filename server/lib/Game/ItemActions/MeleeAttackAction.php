<?php
/**
	Attack the target and apply damage.
*/

namespace Game\ItemActions;

use Game\Item;
use Game\Entity;
use Game\CommunicationService;

class MeleeAttackAction extends ItemAction{
	
	/**
		Called when an item is used on the owner.
	*/
	public function useOnSelf(Item $item, Entity $user) : void{
		// No
	}
	
	/**
		Called when an item is used by the owner on someone else.
	*/
	public function useOnTarget(Item $item, Entity $user, Entity $target) : void {
		if ($user->distanceTo($target) > $item->getRange()){
			return;
		}
		
		$current = $target->getCurrentHP();
		$total_armor = $target->getBaseArmor() + $target->getInventory()->getBonusArmor();
		
		$total_damage = $user->getBaseDamage() + $user->getInventory()->getBonusDamage() - ($total_armor / 2);
		
		if ($total_damage < 0){
			return;
		}
		
		CommunicationService::getInstance()->broadCast([
			'type' => 'swing',
			'attacker_id' => $user->getId(),
			'target_id' => $target->getId(),
			'item_id' => $item->getId()
		]);
		
		$current -= $total_damage;
		if ($current < 0){
			$current = 0;
		}
		
		$target->setCurrentHP($current);
		
		if ($current <= 0){
			$target->getMap()->removeEntity($target);
			CommunicationService::getInstance()->broadCast([
				'type' => 'entity_destroy',
				'id' => $target->getId()
			]);
			
			$user->clearTarget();
		}
	}
	
	/**
		Return a string to describe what this action does.
	*/
	public function describe() : string {
		return 'Swing at a nearby target';
	}
	
	/**
		Return true if this is a weapon action.
	*/
	public function canAttack() : bool {
		return true;
	}
}