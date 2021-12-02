<?php
/**
	Handle an interaction from another entity.
*/

namespace Game\EntityInteractions;

use Game\Entity;

abstract class EntityInteraction {

	abstract public function interact(Entity $me, Entity $interactor) : void;

}