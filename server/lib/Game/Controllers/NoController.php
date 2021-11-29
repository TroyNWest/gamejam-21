<?php
/**
	Do nothing but satsify the requirement for a Controller.
*/

namespace Game\Controllers;

use Game\Entity;

class NoController extends Controller{
	
	public function __construct(string $player_id){
		// Do nothing
	}
	
	public function handleInput(Entity $me, array $packet){
		// Do nothing
	}
	
	public function tick(Entity $me){
		// Do nothing
	}
}