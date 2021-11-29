<?php
/**
	Attack the target and apply damage.
*/

namespace Game\ItemActions;

use Game\EntityFactory;
use Game\Item;
use Game\Entity;
use Game\CommunicationService;

class RangedAttackAction extends ItemAction{
	
	protected string $projectile_code = 'arrow';
	protected float $last_fired = 0.0;
	protected float $fire_delay = 1.0;
	
	/**
		Called when an item is used on the owner.
	*/
	public function useOnSelf(Item $item, Entity $user) : void{
		
	}
	
	/**
		Called when an item is used by the owner on someone else.
	*/
	public function useOnTarget(Item $item, Entity $user, Entity $target) : void {
		/* if ($user->distanceTo($target) > $item->getRange()){
			return;
		} */
		
		$now = microtime(true);
		if ($now - $this->last_fired < $this->fire_delay){
			return;
		}
		
		$projectile = EntityFactory::getInstance()->createProjectile($this->projectile_code, $user, $target, $item->getRange());
		
		CommunicationService::getInstance()->broadCast([
			'type' => 'entity_create',
			'data' => $projectile->serialize()
		]);
		
		$this->last_fired = $now;
	}
	
	/**
		Return a string to describe what this action does.
	*/
	public function describe() : string {
		return 'Fire an arrow at a target';
	}
	
	/**
		Return true if this is a weapon action.
	*/
	public function canAttack() : bool {
		return true;
	}
}