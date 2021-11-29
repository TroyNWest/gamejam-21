<?php
/**
	AI Pilot for an entity.
*/

namespace Game\Controllers;

use Game\Entity;

class AIController extends Controller{
	
	protected float $last_ticks = 0.0;
	protected float $last_move_attempt = 0.0;
	protected float $last_attack_attempt = 0.0;
	protected float $next_attack_offset = 0;
	
	public function __construct(string $player_id){
		// Do nothing
		$this->last_ticks = microtime(true);
		$this->next_attack_offset = rand(1, 5);
	}
	
	public function handleInput(Entity $me, array $packet){
		// Do nothing
	}
	
	public function tick(Entity $me){
		// Search for enemies or do other random tasks
		$position = $me->getPosition();
		$target = $me->getTarget();
		$now = microtime(true);
		
		// Check if we already have a target, check out distance to it
		if ($target){
			$distance = $me->distanceTo($target);
			if ($distance > $me->getDeaggroRange()){
				$me->clearTarget();
			}
			else{
				// We're still within range, check and see if we need to adjust movement to follow or if we can attack
				$weapon = $me->getInventory()->findWeapon();
				if ($weapon && $distance < $weapon->getRange()){
					// Check if we should attack
					if ($now - $this->last_attack_attempt > $this->next_attack_offset){
						$this->last_attack_attempt = $now;
						$weapon->useOnTarget($me, $target);
						$this->next_attack_offset = rand(1, 5);
					}
				}
				elseif ($distance <= 1.0){
					if ($now - $this->last_attack_attempt > $this->next_attack_offset){
						$this->last_attack_attempt = $now;
						$this->next_attack_offset = rand(1, 5);
						$me->punch($target);
					}
				}
				else{
					// Try to move closer if we can
					if ($now - $this->last_move_attempt > 0.3){
						$this->last_move_attempt = $now;
						$target_position = $target->getPosition();
						$me->move($target_position[0], $target_position[1]);
					}
				}
			}
		}
		else{		
			$target = $me->getMap()->getClosestHostileEntity($position[0], $position[1], $me->getFaction());
			if ($target && $me->distanceTo($target) < $me->getAggroRange()){
				// We're close enough to aggro, check line of sight by seeing if we adjust on map move
				$target_position = $target->getPosition();
				$dest = $me->getMap()->getAdjustedMovePoint($position[0], $position[1], $target_position[0], $target_position[1]);
				
				if ($dest[0] == $target_position[0] && $dest[1] == $target_position[1]){
					$me->attack($target);
				}
			}
		}
		
		$this->last_ticks = $now;
	}
}