<?php
/**
	Factions store and calculate relationships between entities.
*/

namespace Game\Factions;

abstract class Faction{
	
	protected string $name = 'Unnamed faction';
	
	public function setName(string $name) : void {
		$this->name = $name;
	}
	
	public function getName() : string {
		return $this->name;
	}
	
	
	/**
		Determine if this faction will attack a member of the compared faction.
	*/
	abstract public function willAttack(Faction $compare) : bool;
	
	/**
		Determine if this faction will defend itself if attacked by a member of the compared faction.
	*/
	abstract public function engageIfAttacked(Faction $compare) : bool;
	
}