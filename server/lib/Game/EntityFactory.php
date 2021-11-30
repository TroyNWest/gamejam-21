<?php
/**
	Helper to create entities.
*/

namespace Game;

use Game\Entity;
use Game\Projectile;
use Game\Factions\FactionFactory;
use Game\Controllers\ControllerFactory;
use Game\Handlers\EventHandlerFactory;

use Game\ItemFactory;

class EntityFactory{
	
	protected array $data = [];
	
	private int $next_id = 1;
	
	private static $instance = null;
	
	public static function getInstance(?string $data_path = null) : EntityFactory {
		if (self::$instance == null){
			if (!$data_path){
				throw new Exception('First instance call requires a data path');
			}
			
			self::$instance = new EntityFactory($data_path);
		}
		
		return self::$instance;
	}
	
	private function __construct(string $data_path){
		$this->data = json_decode(file_get_contents($data_path), true);
		
		if ($this->data === NULL){
			throw new Exception('Invalid entity data');
		}
	}
	
	/**
		Handle event creation.
	*/
	private function processEvents(Entity $entity, array $data){
		foreach($data as $event_type => $codes){
			if (is_string($codes)){
				$handler = EventHandlerFactory::getInstance()->createEventHandler($codes);
				$entity->registerHandler($event_type, $handler);
			}
			elseif (is_array($codes)){
				foreach($codes as $code){
					$handler = EventHandlerFactory::getInstance()->createEventHandler($code);
					$entity->registerHandler($event_type, $handler);
				}
			}
		}
	}

	/**
		Create an Entity.
	*/
	public function createEntity(string $identifier, string $player_id = '') : Entity {
		$data = $this->data[$identifier];
		
		$faction = FactionFactory::getInstance()->createFaction($data['faction']);
		$controller = ControllerFactory::getInstance()->createController($data['controller'], $player_id);
		
		$entity = new Entity($this->next_id++, $data['name'], $data['sprite'], $faction, $controller);
		
		if (isset($data['speed'])){
			$entity->setSpeed($data['speed']);
		}
		
		if (isset($data['max_hp'])){
			$entity->setMaxHP($data['max_hp']);
		}
		
		if (isset($data['current_hp'])){
			$entity->setCurrentHP($data['current_hp']);
		}
		
		if (isset($data['base_damage'])){
			$entity->setBaseDamage($data['base_damage']);
		}
		
		if (isset($data['base_armor'])){
			$entity->setBaseArmor($data['base_armor']);
		}
		
		if (isset($data['aggro_range'])){
			$entity->setAggroRange($data['aggro_range']);
		}
		
		if (isset($data['deaggro_range'])){
			$entity->setDeaggroRange($data['deaggro_range']);
		}
		
		if (isset($data['inventory']) && is_array($data['inventory']['items'])){
			foreach($data['inventory']['items'] as $item_code){
				$item = ItemFactory::getInstance()->createItem($item_code);
				$entity->addItem($item);
			}
		}
		
		if ($data['events']){
			$this->processEvents($entity, $data['events']);
		}
		
		return $entity;
	}
	
	/**
		This method automatically registers the projectile with the $firer's map so it can move towards the target.
	*/
	public function createProjectile(string $identifier, Entity $firer, Entity $target, int $damage, float $max_range = -1.0) : Projectile {
		$data = $this->data[$identifier];
		
		$faction = $firer->getFaction();
		$controller = ControllerFactory::getInstance()->createController($data['controller']);
		
		$projectile = new Projectile($this->next_id++, $data['name'], $data['sprite'], $faction, $controller, $firer, $max_range);
		
		if (isset($data['speed'])){
			$projectile->setSpeed($data['speed']);
		}
		
		$projectile->setBaseDamage($damage);
		
		$source = $firer->getPosition();
		$projectile->setPosition($source[0] + $firer->getWidth() / 2, $source[1] + $firer->getHeight() / 2);
		
		$firer->getMap()->addEntity($projectile);
		
		$target_position = $target->getPosition();
		$projectile->move($target_position[0] + $target->getWidth() / 2, $target_position[1] + $target->getWidth() / 2);
		
		if ($data['events']){
			$this->processEvents($projectile, $data['events']);
		}
		
		return $projectile;
	}
}