<?php
/**
	Represent any object that can exist in the world that's interactable or will change.
*/

namespace Game;

use Game\Entity;
use Game\Inventory;
use Game\Factions\Faction;
use Game\Controllers\Controller;
use Game\Map;
use Game\CommunicationService;

class Entity{
	
	// Id
	protected int $id = 0;
	
	// Human readable name
	protected string $name = 'Unnamed Entity';
	
	// Position
	protected float $x = 0.0;
	protected float $y = 0.0;
	protected int $width = 1;
	protected int $height = 1;
	
	// Move speed in units per second.
	protected float $speed = 0.0;
	protected Path $path;
	protected ?Map $map = null;
	
	// HP
	protected int $max_hp = 10;
	protected int $current_hp = 10;
	
	// Combat
	protected int $base_damage = 1;
	protected int $base_armor = 1;
	protected ?Entity $target = null;
	protected float $aggro_range = 5.0;
	protected float $deaggro_range = 7.0;
	
	// Faction
	protected Faction $faction;
	
	// Appearance
	protected int $sprite_code = 0;
	
	// Inventory
	protected Inventory $inventory;
	
	// Controller
	protected Controller $controller;
	
	// Time elapsed
	protected float $last_ticks = 0;
	
	public function __construct(int $id, string $name, int $sprite_code, Faction $faction, Controller $controller){
		$this->id = $id;
		$this->name = $name;
		$this->sprite_code = $sprite_code;
		$this->path = new Path();
		$this->inventory = new Inventory();
		$this->faction = $faction;
		$this->controller = $controller;
		$this->last_ticks = microtime(true);
	}
	
	public function getId() : int {
		return $this->id;
	}
	
	public function setMap(?Map $map) : void {
		$this->map = $map;
	}
	
	public function getMap() : ?Map {
		return $this->map;
	}
	
	public function setPosition(float $x, float $y) : void {
		$this->x = $x;
		$this->y = $y;
		$this->path->clear();
	}
	
	public function getPosition() : array {
		return [$this->x, $this->y];
	}
	
	public function getWidth() : int {
		return $this->width;
	}
	
	public function getHeight() : int {
		return $this->height;
	}
	
	public function getFaction() : Faction {
		return $this->faction;
	}
	
	public function setFaction(Faction $faction) : void {
		$this->faction = $faction;
	}
	
	public function getInventory() : Inventory {
		return $this->inventory;
	}
	
	public function setBaseDamage(int $base_damage) : void {
		$this->base_damage = $base_damage;
	}
	
	public function getBaseDamage() : int {
		return $this->base_damage;
	}
	
	public function setBaseArmor(int $base_armor) : void {
		$this->base_armor = $base_armor;
	}
	
	public function getBaseArmor() : int {
		return $this->base_armor;
	}
	
	public function setSpeed(float $speed) : void {
		$this->speed = $speed;
	}
	
	public function getSpeed() : float {
		return $this->speed;
	}
	
	public function setAggroRange(float $aggro_range) : void {
		$this->$aggro_range = $aggro_range;
	}
	
	public function getAggroRange() : float {
		return $this->aggro_range;
	}
	
	public function setDeaggroRange(float $deaggro_range) : void {
		$this->deaggro_range = $deaggro_range;
	}
	
	public function getDeaggroRange() : float {
		return $this->deaggro_range;
	}
	
	public function setCurrentHP(int $current_hp) : void {
		$this->current_hp = $current_hp;
		CommunicationService::getInstance()->broadCast([
			'type' => 'entity_update',
			'id' => $this->id,
			'current_hp' => $this->current_hp,
			'max_hp' => $this->max_hp
		]);
	}
	
	public function getCurrentHP() : int {
		return $this->current_hp;
	}
	
	public function setMaxHP(int $max_hp) : void {
		$this->max_hp = $max_hp;
	}
	
	public function getMaxHP() : int {
		return $this->max_hp;
	}
	
	// Calculate linear distance between two entities.
	public function distanceTo(Entity $target) : float {
		$position = $target->getPosition();
		$xdif = $this->x - $position[0];
		$ydif = $this->y - $position[1];
		return sqrt( $xdif * $xdif + $ydif + $ydif );
	}
	
	// Use pathfinding and attempt to reach the given destination
	public function move(float $x, float $y) : void{
		if (!$this->map){
			return;
		}
		
		$target = $this->map->getAdjustedMovePoint($this->x, $this->y, $x, $y);
		
		if ($target[0] == $this->x && $target[1] == $this->y){
			return;
		}
		
		$this->path->clear();
		$this->path->addPoint($target[0], $target[1]);
		
		CommunicationService::getInstance()->broadCast([
			'type' => 'move',
			'id' => $this->id,
			'start' => [ $this->x, $this->y ],
			'end' => $target,
			'speed' => $this->speed
		]);
	}
	
	public function stop() : void{
		$this->path->clear();
		
		CommunicationService::getInstance()->broadCast([
			'type' => 'move',
			'id' => $this->id,
			'start' => [ $this->x, $this->y ],
			'end' => [ $this->x, $this->y ],
			'speed' => $this->speed
		]);
	}
	
	public function addItem(Item $item) : bool{
		return $this->inventory->addItem($item);
	}
	
	public function removeItem(Item $item) : void {
		$this->inventory->removeItem($item);
	}
	
	/** 
		Used by the AI to signal that a target should be attacked.
	*/
	public function attack(Entity $target) : void{
		$this->target = $target; // We will calculate what to do with this
	}
	
	public function clearTarget() : void {
		$this->target = null;
	}
	
	public function getController() : Controller {
		return $this->controller;
	}
	
	public function getTarget() : ?Entity {
		return $this->target;
	}
	
	/**
		Allow an unarmed attack.
	*/
	public function punch(Entity $target) : void {
		$current = $target->getCurrentHP();
		$total_armor = $target->getBaseArmor() + $target->getInventory()->getBonusArmor();
		
		$total_damage = $this->base_damage + $this->inventory->getBonusDamage() - ($total_armor / 2);
		
		if ($total_damage < 0){
			return;
		}
		
		CommunicationService::getInstance()->broadCast([
			'type' => 'swing',
			'attacker_id' => $this->id,
			'target_id' => $target->getId(),
			'item_id' => 0
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
		}
	}
	
	/**
		Allow this entity to process tasks assigned to it.
	*/
	public function tick() : void {
		$this->controller->tick($this);
		
		$now = microtime(true);
		
		// Movement if path
		if ($this->path->size() && $this->map){
			$destination = $this->path->peekFront();
			// Check if we're close enough
			$distance = $this->map->distance($this->x, $this->y, $destination[0], $destination[1]);
			if ($distance < 0.01){
				$this->path->popFront();
				$this->last_ticks = $now;
				return;
			}
			
			// Calculate how much we've moved in the amount of time between frames
			$xdif = $destination[0] - $this->x;
			$ydif = $destination[1] - $this->y;
			
			$elapsed = $now - $this->last_ticks;
			$travelled = $this->speed * $elapsed;
			
			if ($travelled > $distance){
				$this->x = $destination[0];
				$this->y = $destination[1];
				$this->path->popFront();
				$this->last_ticks = $now;
				return;
			}
			
			$xoffset = ($xdif / $distance) * $travelled;
			$yoffset = ($ydif / $distance) * $travelled;
			
			$this->x += $xoffset;
			$this->y += $yoffset;
		}
		
		$this->last_ticks = $now;
	}
	
	public function serialize() : array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'position' => [ $this->x, $this->y ],
			'move' => $this->path->peekFront(),
			'speed' => $this->speed,
			'max_hp' => $this->max_hp,
			'current_hp' => $this->current_hp,
			'sprite' => $this->sprite_code,
			'inventory' => $this->inventory->serialize()
		];
	}
}