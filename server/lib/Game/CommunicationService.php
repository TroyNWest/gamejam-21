<?php
/**
	Provide a global interface for sending clients data.
*/

namespace Game;

use Game\GameServer;
use Game\Entity;

class CommunicationService{

	protected ?GameServer $server;
	
	static private $instance = null;
	
	static public function getInstance() : CommunicationService {
		if (self::$instance == null){
			self::$instance = new CommunicationService();
		}
		
		return self::$instance;
	}
	
	private function __construct(){
		
	}
	
	public function provide(GameServer $server) : void{
		$this->server = $server;
	}
	
	public function broadCast($msg) : void {
		$this->server->broadCast($msg);
	}
	
	public function sendMessageToEntityOwner(Entity $entity, $msg){
		$this->server->sendMessageToEntityOwner($entity, $msg);
	}
}