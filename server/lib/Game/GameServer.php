<?php
namespace Game;

use WSSC\Contracts\ConnectionContract;
use WSSC\Contracts\WebSocket;
use WSSC\Exceptions\WebSocketException;

use Game\Map;
use Game\EntityFactory;
use Game\Entity;
use Game\CommunicationService;
use \Exception;

class GameServer extends WebSocket
{

    /*
     *  If you need to parse URI context like /messanger/chat/JKN324jn4213
     *  You can do so by placing URI parts into an array - $pathParams, when Socket will receive a connection
     *  this variable will be appropriately set to key => value pairs, ex.: ':context' => 'chat'
     *  Otherwise leave $pathParams as an empty array
     */
    public array $pathParams = [];
    private $clients = [];
	private array $players = [];
	private array $maps = [];
	private array $player_storage = [];
	
    private $log;

    /**
     * GameServer constructor.
     *
     * @throws \Exception
     */
    public function __construct(string $config_path)
    {
        // create a log channel
        $this->log = new Logger(__CLASS__);
		
		$config = json_decode(file_get_contents($config_path), true);
		
		if (!$config){
			throw new Exception('Invalid GameServer config path');
		}
		
		// Construct factories with data
		if (is_array($config['factories'])){
			foreach($config['factories'] as $factory => $data_path){
				$factory::getInstance($data_path);
			}
		}
		
		// Load a starting map
		$this->maps[] = new Map($config['map']);
		
		// Let the communication service know where we're at so it can service requests to send data.
		CommunicationService::getInstance()->provide($this);
    }

	/**
		Called by the WebSocketServer when a new client connects.
		@param ConnectionContract $conn The new connection.
	*/
    public function onOpen(ConnectionContract $conn)
    {
		$id = $conn->getUniqueSocketId();
        $this->clients[$id] = $conn;
        $this->log->debug('Client connected, total clients: ' . count($this->clients));
		
		// Send map data
		$conn->send(json_encode([
			'type' => 'map',
			'map' => $this->maps[0]->serialize()
		]));
    }

	/**
		Called when we recieve data from a client.
		@throws Exception If $msg is not valid JSON or evaluates to something falsy.
		@param ConnectionContract $sender The connection we received data from.
		@param string $msg The message content. For our application this should be JSON.
	*/
    public function onMessage(ConnectionContract $sender, $msg)
    {
        $this->log->debug('Received message:  ' . $msg);
        
		$packet = json_decode($msg, true);
		
		if (!$packet){
			$this->log->debug('Ignored, Invalid JSON');
			return;
		}
		
		if ($packet['type'] == 'key'){
			$id = $sender->getUniqueSocketId();
			if (array_key_exists($packet['value'], $this->player_storage)){
				$player = $this->player_storage[ $packet['value'] ];
			}
			else{
				$player = EntityFactory::getInstance()->createEntity('player', $id);
				$player->setPosition(rand(1, 50), rand(1, 50));
				$this->player_storage[ $packet['value'] ] = $player;
			}
			
			$this->players[$id] = $player;
			$this->maps[0]->addEntity($player);
			
			// broadcast player join data for the new entity to everyone
			$sender->broadCast(json_encode([
				'type' => 'entity_create',
				'data' => $player->serialize()
			]));
			
			$sender->send(json_encode([
				'type' => 'key',
				'value' => $packet['value'],
				'id' => $player->getId()
			]));
		}
		else{
			$player = $this->players[$sender->getUniqueSocketId()];
		}
		
		// Cheat codes for testing
		if ($packet['type'] == 'spawn' && $player){
			$entity = EntityFactory::getInstance()->createEntity($packet['code'], '');
			$entity->setPosition($packet['x'], $packet['y']);
			$player->getMap()->addEntity($entity);
			
			$sender->broadCast(json_encode([
				'type' => 'entity_create',
				'data' => $entity->serialize()
			]));
		}
		elseif ($packet['type'] == 'give' && $player){
			$item = ItemFactory::getInstance()->createItem($packet['code']);
			if ($player->getInventory()->addItem($item)){			
				$sender->broadCast(json_encode([
					'type' => 'item_create',
					'owner_id' => $player->getId(),
					'data' => $item->serialize()
				]));
			}
		}
		elseif ($packet['type'] == 'jump' && $player){
			$player->setPosition($packet['x'], $packet['y']);
			$sender->broadCast(json_encode([
					'type' => 'entity_update',
					'id' => $player->getId(),
					'position' => $player->getPosition()
				]));
		}
		
		if ($player){
			$player->getController()->handleInput($player, $packet);
		}
    }

	/**
		Called when a client disconnected.
		@param ConnectionContract The connection that closed.
	*/
    public function onClose(ConnectionContract $conn)
    {
		$id = $conn->getUniqueSocketId();
        unset($this->clients[$id]);
        $this->log->debug('Disconnect, total clients: ' . count($this->clients));
        $conn->close();
		
		$player = $this->players[$id];
		if ($player){
			if ($player->getMap()){
				$player->getMap()->removeEntity($player);
			}
			$conn->broadCast(json_encode([
				'type' => 'entity_destroy',
				'id' => $player->getId()
			]));
		}
		
		unset($this->players[$id]);
    }

    /**
		Called when there's an error processing data from the WebSocketServer.
     * @param ConnectionContract $conn
     * @param WebSocketException $ex
     */
    public function onError(ConnectionContract $conn, WebSocketException $ex)
    {
        $this->log->debug('Error occurred: ' . $ex->printStack());
    }

    /**
     * You may want to implement these methods to bring ping/pong events
     *
     * @param ConnectionContract $conn
     * @param string $msg
     */
    public function onPing(ConnectionContract $conn, $msg)
    {
    }

    /**
     * @param ConnectionContract $conn
     * @param $msg
     * @return mixed
     */
    public function onPong(ConnectionContract $conn, $msg)
    {
    }
	
	/**
		Allow the server time to update the simulation.
		This will fire as many times as we can during the server loop.
	*/
	public function tick(){
		foreach($this->maps as $map){
			$map->tick();
		}
	}
	
	/**
		If an entity belongs to a player then send a message to them. 
	*/
	public function sendMessageToEntityOwner(Entity $entity, $msg){
		if (is_array($msg)){
			$msg = json_encode($msg);
		}
		
		$index = array_search($entity, $this->players, true);
		
		if ($index !== false){
			$this->clients[$index]->send($msg);
		}
	}
	
	/**
		Send a message to all connected players.
	*/
	public function broadCast($msg){
		if (is_array($msg)){
			$msg = json_encode($msg);
		}
		
		end($this->clients)->broadCast($msg);
	}
}