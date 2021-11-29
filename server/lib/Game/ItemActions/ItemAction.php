<?php
/**
	Store some code that explains what to do when an item is used or activated.
*/

namespace Game\ItemActions;

use Game\Item;
use Game\Entity;

abstract class ItemAction{
	
	/**
		Called when an item is used on the owner.
	*/
	abstract public function useOnSelf(Item $item, Entity $user) : void;
	
	/**
		Called when an item is used by the owner on someone else.
	*/
	abstract public function useOnTarget(Item $item, Entity $user, Entity $target) : void;
	
	/**
		Return a string to describe what this action does.
	*/
	abstract public function describe() : string;
	
	/**
		Return true if this is a weapon action.
	*/
	public function canAttack() : bool {
		return false;
	}
}