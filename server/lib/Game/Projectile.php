<?php
/**
	Special class that detects collision with other hostile entities.
*/

namespace Game;

use Game\Entity;
use Game\Factions\Faction;
use Game\Controllers\Controller;

class Projectile extends Entity {
	
	protected bool $hint_projectile_rotate = true;
	protected float $hint_rotate_offset_degrees = -45.0;
	protected float $max_range = -1.0;
	protected float $start_x = 0.0;
	protected float $start_y = 0.0;
	
	public function __construct(int $id, string $name, int $sprite_code, Faction $faction, Controller $controller, float $max_range = -1.0){
		parent::__construct($id, $name, $sprite_code, $faction, $controller);
		
		$this->max_range = $max_range;
	}
	
	public function setPosition(float $x, float $y) : void {
		parent::setPosition($x, $y);
		
		$this->start_x = $x;
		$this->start_y = $y;
	}
	
	public function tick() : void{
		parent::tick();
		
		if (!$this->map) {
			return;
		}
		
		// Check if we're out of range
		if ($this->max_range > 0 && $this->map->distance($this->start_x, $this->start_y, $this->x, $this->y) > $this->max_range){
			$this->map->removeEntity($this);
			CommunicationService::getInstance()->broadCast([
				'type' => 'entity_destroy',
				'id' => $this->id
			]);
			return;
		}
		
		// Check for collision
		$target = $this->map->getHostileEntityAtPoint($this->x + $this->width / 2, $this->y + $this->height / 2, $this->faction);
		if ($target){
			$current = $target->getCurrentHP();
			$total_armor = $target->getBaseArmor() + $target->getInventory()->getBonusArmor();
			
			$total_damage = $this->base_damage - ($total_armor / 2);
			
			if ($total_damage < 0){
				$this->map->removeEntity($this);
				CommunicationService::getInstance()->broadCast([
					'type' => 'entity_destroy',
					'id' => $this->id
				]);
				return;
			}
			
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
			}
			
			$this->map->removeEntity($this);
			CommunicationService::getInstance()->broadCast([
				'type' => 'entity_destroy',
				'id' => $this->id
			]);
			return;
		}
		
		// If we reached our destination then remove us as impacted
		if ($this->path->size() <= 0){
			$this->map->removeEntity($this);
			CommunicationService::getInstance()->broadCast([
				'type' => 'entity_destroy',
				'id' => $this->id
			]);
		}
	}
	
	public function serialize() : array {
		return [
			'id' => $this->id,
			'position' => [ $this->x, $this->y ],
			'sprite' => $this->sprite_code,
			'move' => $this->path->peekFront(),
			'speed' => $this->speed,
			'rotate' => $this->hint_projectile_rotate ? $this->hint_rotate_offset_degrees : false
		];
	}
}	