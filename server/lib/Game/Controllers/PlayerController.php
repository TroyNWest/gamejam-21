<?php
/**
	Accept inputs from a player.
*/

namespace Game\Controllers;

use Game\Entity;
use Game\Item;
use Game\CommunicationService;

class PlayerController extends Controller{
	
	public function __construct(string $player_id){
		// Do nothing, for now
	}
	
	public function handleInput(Entity $me, array $packet){
		if ($packet['type'] == 'move'){
			$me->move(floatval($packet['x']), floatval($packet['y']));
		}
		elseif ($packet['type'] == 'use'){
			$item = $me->getInventory()->findItemById(intval($packet['item_id']));
			$target_id = intval($packet['target_id']);
			
			if (!$item){
				return;
			}
			
			if ($target_id){
				$target = $me->getMap()->findEntityById($target_id);
				if ($target){
					$item->useOnTarget($me, $target);
				}
			}
			else{
				$item->useOnSelf($me);
			}
		}
		elseif ($packet['type'] == 'interact'){
			$target = $me->getMap()->findEntityById(intval($packet['target_id']));
			
			if (!$target){
				return;
			}
			
			$target->interact($me);
		}
		// Why do I even have to do this?
		elseif ($packet['type'] == 'poop'){
			CommunicationService::getInstance()->sendMessageToEntityOwner($me, [
				'type' => 'no-troy',
				'just' => 'no'
			]);
		}
	}
	
	public function tick(Entity $me){
		// Do nothing
	}
}