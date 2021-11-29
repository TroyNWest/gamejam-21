<?php
/**
	Used by player entities. Essentially neutral since the player will issue their own commands.
*/

namespace Game\Factions;

use Game\Factions\Faction;

class EnemyFaction extends Faction{
	
	public function __construct(){
		$this->setName('Enemy');
	}
	
	/**
		Determine if this faction will attack a member of the compared faction.
	*/
	public function willAttack(Faction $compare) : bool {
		return $compare->getName() != 'Enemy';
	}
	
	/**
		Determine if this faction will defend itself if attacked by a member of the compared faction.
	*/
	public function engageIfAttacked(Faction $compare) : bool {
		return true;
	}
	
}