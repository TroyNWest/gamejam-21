<?php
/**
	Do nothing.
*/

namespace Game\ItemActions;

use Game\Item;
use Game\Entity;

class DoNothingAction extends ItemAction{
	
	/**
		Called when an item is used on the owner.
	*/
	public function useOnSelf(Item $item, Entity $user) : void{
		
	}
	
	/**
		Called when an item is used by the owner on someone else.
	*/
	public function useOnTarget(Item $item, Entity $user, Entity $target) : void{

	}		
	
	/**
		Return a string to describe what this action does.
	*/
	public function describe() : string {
		return 'Does nothing';
	}
	
}