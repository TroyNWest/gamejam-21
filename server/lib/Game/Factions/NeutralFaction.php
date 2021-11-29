<?php
/**
	Totally Neutral, never attacks.
*/

namespace Game\Factions;

use Game\Factions\Faction;

class NeutralFaction extends Faction{
	
	public function __construct(){
		$this->setName('Neutral');
	}
	
	/**
		Determine if this faction will attack a member of the compared faction.
	*/
	public function willAttack(Faction $compare) : bool {
		return false;
	}
	
	/**
		Determine if this faction will defend itself if attacked by a member of the compared faction.
	*/
	public function engageIfAttacked(Faction $compare) : bool {
		return false;
	}
	
}