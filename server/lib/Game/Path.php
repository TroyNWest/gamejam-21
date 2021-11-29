<?php
/**
	A common storage container for nodes and a way to quickly process on them.
*/

namespace Game;

class Path{
	
	protected array $points = [];
	
	public function clear(){
		$this->points = [];
	}
	
	public function addPoint($x, $y){
		$this->points[] = [$x, $y];
	}
	
	public function addPointEnd($x, $y) : void{
		$this->points[count($this->points)] = [$x, $y];
	}
	
	public function peekFront() : ?array {
		if (count($this->points)){
			return $this->points[0];
		}
		
		return null;
	}
	
	public function popFront() : array{
		return array_shift($this->points);
	}
	
	public function getPoints() : array{
		return $this->points;
	}
	
	public function size() : int {
		return count($this->points);
	}
}