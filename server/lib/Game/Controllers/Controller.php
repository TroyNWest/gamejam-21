<?php
/**
	Provide a method of instructing an Entity on how to behave.
*/

namespace Game\Controllers;

use Game\Entity;

abstract class Controller{
	
	// For Players, process incoming command packets
	abstract public function handleInput(Entity $me, array $packet);
	
	// For AI, allow the controller to process
	abstract public function tick(Entity $me);
	
}