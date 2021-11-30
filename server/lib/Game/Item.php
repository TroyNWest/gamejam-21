<?php
/**
	Information about an item.
*/

namespace Game;

use Game\ItemActions\ItemAction;
use Game\Entity;

class Item{
	
	protected int $id = 0;
	
	protected string $name = 'Unnamed item';
	
	protected int $sprite_code = 0;
	
	protected int $damage = 0;
	protected int $bonus_damage = 0;
	
	protected int $armor = 0;
	
	protected float $range = 1.0; // Used by the AI to determine when it's within range to attack.
	
	protected ItemAction $on_use;
	
	public function __construct(int $id, string $name, int $sprite_code, ItemAction $actions){
		$this->id = $id;
		$this->name = $name;
		$this->sprite_code = $sprite_code;
		$this->on_use = $actions;
	}
	
	public function getId() : int {
		return $this->id;
	}
	
	public function getName() : string {
		return $this->name;
	}
	
	public function getSpriteCode() : int {
		return $this->sprite_code;
	}
	
	public function setDamage(int $damage) : void {
		$this->damage = $damage;
	}
	
	public function setBonusDamage(int $bonus_damage) : void {
		$this->bonus_damage = $bonus_damage;
	}
	
	public function getBonusDamage() : int {
		return $this->bonus_damage;
	}
	
	public function getDamage() : int {
		return $this->damage;
	}
	
	public function setArmor(int $armor) : void {
		$this->armor = $armor;
	}
	
	public function getArmor() : int {
		return $this->armor;
	}
	
	public function setRange(float $range) : void {
		$this->range = $range;
	}
	
	public function getRange() : float {
		return $this->range;
	}
	
	public function isWeapon() : bool {
		return $this->on_use->canAttack();
	}
	
	public function useOnSelf(Entity $user) : void{
		$this->on_use->useOnSelf($this, $user);
	}
	
	public function useOnTarget(Entity $user, Entity $target) : void{
		$this->on_use->useOnTarget($this, $user, $target);
	}
	
	public function serialize() : array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'sprite' => $this->sprite_code,
			'damage' => $this->damage,
			'armor' => $this->armor,
			'use' => $this->on_use->describe()
		];
	}
}