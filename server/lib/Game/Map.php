<?php
/**
	Data storage and pathfinding routines.
*/

namespace Game;

use Game\Entity;
use Game\Factions\Faction;

class Map{
	protected array $data = [];
	private array $entities = [];
	
	public function __construct(string $data_path){
		$this->data = json_decode(file_get_contents($data_path), true);
	}
	
	public function addEntity(Entity $entity){
		$this->entities[] = $entity;
		$entity->setMap($this);
	}
	
	public function removeEntity(Entity $entity){
		$index = array_search($entity, $this->entities, true);
		if ($index !== false){
			unset($this->entities[$index]);
		}
		
		$entity->setMap(null);
	}
	
	public function findEntityById(int $id) : ?Entity {
		foreach($this->entities as $entity){
			if ($entity->getId() == $id){
				return $entity;
			}
		}
		
		return null;
	}
	
	/**
		Select an entity that would attack the filter faction.
	*/
	public function getHostileEntityAtPoint(int $x, int $y, Faction $filter) : ?Entity {
		$result = null;
		foreach($this->entities as $entity){
			if (get_class($entity) == 'Game\\Projectile'){
				continue;
			}
			
			if ($entity->getFaction()->willAttack($filter)){
				$position = $entity->getPosition();
				if ($x > $position[0] && $x < $position[0] + $entity->getWidth() && 
					$y > $position[1] && $y < $position[1] + $entity->getHeight()
				){
					$result = $entity;
					break;
				}
			}
		}
		
		return $result;
	}
	
	/**
		Get the nearest hostile entity.
	*/
	public function getClosestHostileEntity(int $x, int $y, Faction $filter) : ?Entity {
		$result = null;
		$closest = -1.0;
		foreach($this->entities as $entity){
			if (get_class($entity) == 'Game\\Projectile'){
				continue;
			}
			
			if ($filter->willAttack($entity->getFaction())){
				$position = $entity->getPosition();
				$distance = $this->distance($x, $y, $position[0], $position[1]);
				
				if ($closest < 0.0 || $distance < $closest){
					$result = $entity;
					$closest = $distance;
				}
			}
		}
		
		return $result;
	}
	
	public function getAdjustedMovePoint(float $startX, float $startY, float $endX, float $endY) : array {
		$movement_line = [$startX, $startY, $endX, $endY];
		
		// Walk through map data and find all line segments that are collidable. 
		// Test each for collision with our desired movement. Each time we calculate a new point, test for distance to start and return the one closest.
		$closest = -1.0;
		$ix = 0.0;
		$iy = 0.0;
		foreach($this->data['layers'] as $layer){
			if ($layer['name'] == 'collision_stuff'){
				foreach($layer['objects'] as $object){
					$lines = [];
					/*if (is_array($object['properties'])){
						foreach($object['properties'] as $property){
							if ($property['name'] == 'collision' && $property['value']){
								$lines = $this->getLineSegments($object);
							}
							elseif ($property['name'] == 'locked' && $property['value']){
								$lines = $this->getLineSegments($object);
							}
						}
					}*/
					$lines = $this->getLineSegments($object);
					
					if (count($lines)){
						foreach($lines as $segment){
							if ($this->calculateLineSegmentIntersection($movement_line, $segment, $ix, $iy)){
								$dist = $this->distance($startX, $startY, $ix, $iy);
								if ($closest < 0.0 || $dist < $closest){
									$endX = $ix;
									$endY = $iy;
									$closest = $dist;
								}
							}
						}
					}
				}
			}
		}
		
		// We changed the end so we need to fuzz the endpoint a little bit away from collision so we don't get stuck.
		
		$fuzz_distance = 0.2;
		$xdif = $endX - $startX;
		$ydif = $endY - $startY;
		$dist = $this->distance($startX, $startY, $endX, $endY);
			
		if ($dist > $fuzz_distance && ($endX != $movement_line[2] || $endY != $movement_line[3])){
			//$t = [$endX, $endY]; // For debug
			
			$endX = $startX + $xdif - (($xdif < 0 ? -1.0 : 1.0) * $fuzz_distance );
			$endY = $startY + $ydif - (($ydif < 0 ? -1.0 : 1.0) * $fuzz_distance );
			
			/* echo "[Map] Shunting adjusted end from ({$movement_line[2]}, {$movement_line[3]}) to ({$t[0]}, {$t[1]}) to ({$endX}, {$endY})\n";
			echo "[Map] Fuzz: {$fuzz_distance} Xdif: {$xdif} Ydif: {$ydif} Distance: {$dist}\n"; */
		}
		
		return [$endX, $endY];
	}
	
	/**
		Calculate distance between 2 points.
	*/
	public function distance(float $x1, float $y1, float $x2, float $y2) : float {
		$xdif = $x1 - $x2;
		$ydif = $y1 - $y2;
		return sqrt($xdif * $xdif + $ydif * $ydif);
	}
	
	/**
		Return an array of line segments for testing.
	*/
	private function getLineSegments(array $object) : array {
		$result = [];
		
		$x = $object['x'];
		$y = $object['y'];
		
		// Complex polygon with offset values
		if (is_array($object['polygon'])){
			$size = count($object['polygon']);
			for($i = 1; $i < $size; $i ++){
				$prev = $object['polygon'][$i - 1];
				$point = $object['polygon'][$i];
				
				$result[] = [$x + $prev['x'], $y + $prev['y'], 
							$x + $point['x'], $y + $point['y']];
			}
			
			if ($size){
				$result[] = [$x + $object['polygon'][$size - 1]['x'], $y + $object['polygon'][$size - 1]['y'], 
							$x + $object['polygon'][0]['x'], $y + $object['polygon'][0]['y'] ];
			}
		}
		// Normal rectangle using width and height
		else{
			$width = $object['width'];
			$height = $object['height'];
			
			$result[] = [$x, $y, $x + $width, $y];
			$result[] = [$x + $width, $y, $x + $width, $y + $height];
			$result[] = [$x + $width, $y + $height, $x, $y + $height];
			$result[] = [$x, $y + $height, $x, $y];
		}
		
		// Values are stored in pixels, we need to adjust them to units
		foreach($result as &$line){
			foreach($line as &$value){
				$value /= 8;
			}
		}
		
		return $result;
	}
	
	/**
		Determines if 2 line segments intersect. Returns true if yes. Populates $ix and $iy with intersection point if true.
		Line 1 = (x1, y1)(x2, y2)
		Line 2 = (x3, y3)(x4, y4)
		https://en.wikipedia.org/wiki/Line%E2%80%93line_intersection#Given_two_points_on_each_line_segment
	*/
	private function calculateLineSegmentIntersection(array $line1, array $line2, float &$ix, float &$iy) : bool {
		//$t = ((x1 - x3) * (y3 - y4) - (y1 - y3) * (x3 - x4)) / ((x1 - x2) * (y3 - y4) - (y1 - y2) * (x3 - x4));
		//$u = ((x1 - x3) * (y1 - y2) - (y1 - y3) * (x1 - x2)) / ((x1 - x2) * (y3 - y4) - (y1 - y2) * (x3 - x4));
		
		if ((($line1[0] - $line1[2]) * ($line2[1] - $line2[3]) - ($line1[1] - $line1[3]) * ($line2[0] - $line2[2])) == 0 || 
			 (($line1[0] - $line1[2]) * ($line2[1] - $line2[3]) - ($line1[1] - $line1[3]) * ($line2[0] - $line2[2])) == 0)
		{
			return false;	 
		}
		
		$t = (($line1[0] - $line2[0]) * ($line2[1] - $line2[3]) - ($line1[1] - $line2[1]) * ($line2[0] - $line2[2])) / 
			 (($line1[0] - $line1[2]) * ($line2[1] - $line2[3]) - ($line1[1] - $line1[3]) * ($line2[0] - $line2[2]));
		$u = (($line1[0] - $line2[0]) * ($line1[1] - $line1[3]) - ($line1[1] - $line2[1]) * ($line1[0] - $line1[2])) / 
			 (($line1[0] - $line1[2]) * ($line2[1] - $line2[3]) - ($line1[1] - $line1[3]) * ($line2[0] - $line2[2]));
		
		if (($t < 0.0 || $t > 1.0) || ($u < 0.0 || $u > 1.0)){
			return false;
		}
		
		$ix = $line1[0] + $t * ($line1[2] - $line1[0]);
		$iy = $line1[1] + $t * ($line1[3] - $line1[1]);
		return true;
	}
	
	public function tick(){
		foreach($this->entities as $entity){
			$entity->tick();
		}
	}
	
	public function serialize() : array {
		
		$data = [];
		foreach($this->entities as $entity){
			$data[] = $entity->serialize();
		}
		
		return [
			'id' => 1, //$this->data,
			'entities' => $data
		];
	}
}