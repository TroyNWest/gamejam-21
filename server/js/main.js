var image_lookup = {};	// Cache of loaded images in request_image.
var tile_scale = 32; 	// Pixels per tile when rendering (8 by default)
var last_frame_time = new Date(); // Used to track elapsed time between frames.
var entities = {};		// Storage for the entities on the map.
var tilemap = null;		// Storage for the tilemap image
var map = null;			// Storage for the map data.
var socket = null;		// Storage for the socket we're connected on.
var player_id = null; 	// Identify which entity is our player. This is received from the response to our key request.
var mouse_x, mouse_y; 	// Storage for mouse position, in pixels, relative to entire body top left
var swings = [];		// Storage for tiny special effects that show a weapon is being used.
var ui_fill_color = '#888888'; // Color used by fill (and sometimes stroke) to draw UI elements.


/**
	Request an image and trigger a callback when it's loaded.
	The callback will occur instantly if the image is already loaded.
*/
function request_image(url, callback){
	if (image_lookup[url]){
		callback(image_lookup[url]);
		return;
	}
	
	var i = new Image();
	i.src = url;
	i.onload = function(e){
		image_lookup[url] = i;
		callback(i);
	};
}

/**
	Create a "swing" effect for when a melee attack occurs so there is a tiny icon to indicate attacks are happening.
*/
function handle_swing(attacker_id, item_id){
	var attacker = entities[attacker_id];
	if (!attacker) return;
	var item = null;
	for (var i = 0; i < attacker.inventory.items.length; i++){
		if (attacker.inventory.items[i].id == item_id){
			item = attacker.inventory.items[i];
			break;
		}
	}
	if (!item) return;
	swings.push({
		x : attacker.position[0],
		y : attacker.position[1] - tile_scale - tile_scale / 2,
		sprite : item.sprite,
		start : new Date()
	});
}

/**
	Calculate the x,y to read a sprite from in the tilemap.
*/
function getSpriteOffset(id){
	if (id == 0){
		return [8,8];
	}
	
	id --; // Offset by 1 for phaser
	
	var x = id % 16;
	var y = parseInt(id / 16);
	
	return [x * 8, y * 8];
}

/**
	Calculate distance between 2 points.
*/
function distance(x1,y1, x2,y2){
	var xdif = x1 - x2;
	var ydif = y1 - y2;
	return Math.sqrt(xdif * xdif + ydif * ydif);
}

/**
	Calculate entity movement over the elapsed time.
	@param object entity The entity to check on moving.
	@param int elapsed The time elapsed for this move in milliseconds.
*/
function move_entity(entity, elapsed){
	if (!entity.move){
		return;
	}
	
	var dist = distance(entity.position[0] * tile_scale, entity.position[1] * tile_scale, entity.move[0] * tile_scale, entity.move[1] * tile_scale);
	
	if (dist < 0.1){
		entity.move = null;
		return;
	}
	
	var xdif = entity.move[0] - entity.position[0];
	var ydif = entity.move[1] - entity.position[1];
	
	xdif *= tile_scale;
	ydif *= tile_scale;
	
	var travelled = (entity.speed * tile_scale) * (elapsed / 1000);
	
	if (travelled > dist){
		entity.position[0] = entity.move[0];
		entity.position[1] = entity.move[1];
		entity.move = null;
		return;
	}
	
	var xoffset = (xdif / dist) * travelled;
	var yoffset = (ydif / dist) * travelled;
	
	entity.position[0] += xoffset / tile_scale;
	entity.position[1] += yoffset / tile_scale;
}

/**
	Render map data if it's available.
*/
function render_map(context){
	if (!map){
		return false;
	}
	
	var canvas_width = context.canvas.clientWidth;
	var canvas_height = context.canvas.clientHeight;
	context.fillStyle = map.backgroundcolor;
	context.fillRect(0, 0, canvas_width, canvas_height);
	
	var map_width = map.width;
	var map_height = map.height;
	
	var data = map.layers[0].data;
	var i = 0;
	var bounds;
	
	for (var y = 0; y < map_height; y ++){
		for (var x = 0; x < map_width; x ++){
			i = y * map_width + x;
			bounds = getSpriteOffset(data[i]);
			context.drawImage(tilemap, bounds[0] , bounds[1], 8, 8, x * tile_scale, y * tile_scale, tile_scale, tile_scale);
		}
	}
	
	
	return true;
}

/**
	Return the rotation to apply to this sprite so it will point towards its destination.
	@param float initial_rotation The rotation applied already by how the image is illustrated this is in degrees!
	@param array position The current or starting position.
	@param array destination The destination of this object.
	
	@return float Radians required by the rotate function.
*/
function calculate_rotation(initial_rotation, position, destination){
	initial_rotation *= Math.PI / 180;
	var xdif = destination[0] - position[0];
	var ydif = destination[1] - position[1];
	return Math.atan2(ydif, xdif) - initial_rotation;
}

/**
	Bake a frame processing function so we don't have to pass anything to the interval.
	@param Canvas2DContext context
*/
function bake_frame(context){
	return function(){
		var now = new Date();
		var elapsed = now - last_frame_time;
		last_frame_time = now;
		
		var entity, bounds;
		for (var id in entities){
			if (!entities.hasOwnProperty(id)) continue;
			entity = entities[id];
			move_entity(entity, elapsed);
		}
		
		for (var i = 0; i < swings.length; i ++){
			if (now - swings[i].start > 500){
				swings.splice(i, 1);
			}
		}
		
		if (!tilemap) return;
		
		// Clear all, render map
		if (!render_map(context)){
			context.clearRect(0, 0, 800, 600);
		}
		
		// Render entities
		var x,y,width,height,rotation;
		for(var id in entities){
			if (!entities.hasOwnProperty(id)) continue;
			entity = entities[id];
			bounds = getSpriteOffset(entity.sprite);
			x = parseInt(entity.position[0] * tile_scale);
			y =  parseInt(entity.position[1] * tile_scale);
			width = tile_scale;
			height = tile_scale;
			
			if (entity.hint == 'loot'){
				width /= 2;
				height /= 2;
				y += height * Math.sin(now / 1000);
			}
			
			if (typeof entity.rotate == 'number' && entity.move){
				rotation = calculate_rotation(entity.rotate, entity.position, entity.move);
				context.translate(x - tile_scale / 2, y - tile_scale);
				context.rotate(rotation);
			}
			else{
				context.translate(x - tile_scale / 2, y - tile_scale);
			}
			
			context.drawImage(tilemap, bounds[0], bounds[1], 8, 8, 0, 0, width, height);
			
			context.setTransform(1, 0, 0, 1, 0, 0); // Force transform back to identity
			
			
			if (entity.current_hp != entity.max_hp){
				context.fillStyle = 'red';
				context.fillRect(x - tile_scale / 2, y - 2 - tile_scale, tile_scale, 2);
				
				context.fillStyle = 'green';
				context.fillRect(x - tile_scale / 2, y - 2 - tile_scale, parseInt(tile_scale * (entity.current_hp / entity.max_hp)), 2);
			}
		}
		
		// Render swing effects
		var swing;
		for(var i = 0; i < swings.length; i ++){
			swing = swings[i];
			x = parseInt(swing.x * tile_scale);
			y =  parseInt(swing.y * tile_scale);
			bounds = getSpriteOffset(swing.sprite);
			
			context.drawImage(tilemap, bounds[0], bounds[1], 8, 8, x, y, parseInt(tile_scale / 2), parseInt(tile_scale / 2));
		}
		
		// Render UI
		if (player_id){
			var player = entities[ player_id ];
			if (player){
				
				for (var i = 0; i < player.inventory.capacity; i ++){
					var item = player.inventory.items[i];
					if (item){
						context.fillStyle = map.backgroundcolor;
						context.strokeStyle = map.backgroundcolor;
				
						bounds = getSpriteOffset(item.sprite);
						context.fillRect(parseInt(i * tile_scale * 2), 0, parseInt(tile_scale * 2), parseInt(tile_scale * 2));
						context.drawImage(tilemap, bounds[0], bounds[1], 8, 8, parseInt(i * tile_scale * 2) + 1, 1, parseInt(tile_scale * 2), parseInt(tile_scale * 2));
						
						context.fillStyle = ui_fill_color;
						context.strokeStyle = ui_fill_color;
						
						context.fillText(i + 1, parseInt(i * tile_scale * 2) + tile_scale, parseInt(tile_scale * 2) + 10);
					}
				}
			}
		}
	}
}


/**
	Logic to process packets.
	@param object packet The packet received.
*/
function handle_packet(packet){
	if (packet.type == 'entity_create'){
		entities[ packet.data.id ] = packet.data;
	}
	else if (packet.type == 'entity_destroy'){
		delete entities[ packet.id ];
	}
	else if (packet.type == 'move'){
		var entity = entities[ packet.id ];
		if (!entity) return;
		entity.position = packet.start;
		entity.move = packet.end;
		entity.speed = packet.speed;
	}
	else if (packet.type == 'map'){
		var entity;
		for (var i = 0; i < packet.map.entities.length; i ++){
			entity = packet.map.entities[i];
			entities[ entity.id ] = entity;
		}
	}
	else if (packet.type == 'key'){
		player_id = packet.id;
	}
	else if (packet.type == 'swing'){
		handle_swing(packet.attacker_id, packet.item_id);
	}
	else if (packet.type == 'item_create'){
		var entity = entities[ packet.owner_id ];
		if (!entity) return;
		entity.inventory.items.push(packet.data);
	}
	else if (packet.type == 'entity_update'){
		var entity = entities[ packet.id ];
		if (!entity) return;
		
		if (typeof packet.current_hp == 'number'){
			entity.current_hp = packet.current_hp;
		}
		else if (typeof packet.max_hp == 'number'){
			entity.max_hp =  packet.max_hp;
		}
		else if (typeof packet.position == 'object'){
			entity.position = packet.position;
		}
	}
	else if (packet.type == 'item_remove'){
		var entity = entities[ packet.owner_id ];
		if (!entity) return;
		
		for(var i = 0; i < entity.inventory.items.length; i ++){
			if (entity.inventory.items[i].id == packet.id){
				entity.inventory.items.splice(i, 1);
				break;
			}
		}
	}
}

/**
	Get json file.
	@param string method GET or POST.
	@param string url The URL to request.
	@param function resolve What to do with the result.
	@param function reject Handle errors.
*/
function getJSONFile(method, url, resolve, reject) {
	let xhr = new XMLHttpRequest();
	xhr.open(method, url);
	xhr.onload = function () {
		if (this.status >= 200 && this.status < 300) {
			resolve(xhr.response);
		} else {
			reject({
				status: this.status,
				statusText: xhr.statusText
			});
		}
	};
	xhr.onerror = function () {
		reject({
			status: this.status,
			statusText: xhr.statusText
		});
	};
	xhr.send();
}

/**
	Use a mousedown event to trigger a move request.
*/
function handle_mouse_down(e){
	e.preventDefault();
	if (!socket){
		return;
	}
	
	var target = find_entity_at_point(e.layerX, e.layerY);
	if (target){
		socket.send(JSON.stringify({
			type : 'interact',
			target_id : target.id
		}));
	}
	else{
		socket.send(JSON.stringify({
			type : 'move',
			x : e.layerX / tile_scale,
			y : e.layerY / tile_scale
		}));
	}
}

/**
	Handle keypress events.
*/
function handle_key(key){
	if (!parseInt(key) || !player_id){
		return;
	}
	
	var index = parseInt(key) - 1;
	if (index >= entities[player_id].inventory.items.length){
		return;
	}
	
	var item = entities[player_id].inventory.items[index];
	if (!item){
		return;
	}
	
	var target = find_entity_at_point(mouse_x, mouse_y);
	
	if (target && target.id == player_id){
		target = null;
	}
	
	socket.send(JSON.stringify({
		type : 'use',
		item_id : item.id,
		target_id : target ? target.id : 0
	}));
}

/**
	Search for an entity at the given point in pixels.
	This is used to select an entity when the mouse is over them.
*/
function find_entity_at_point(px, py){
	var entity, x, y, width, height;
	for(var id in entities){
		if (!entities.hasOwnProperty(id)) continue;
		entity = entities[id];
		
		x = entity.position[0] * tile_scale - tile_scale / 2;
		y = entity.position[1] * tile_scale - tile_scale;
		width = tile_scale;
		height = tile_scale;
		
		if (px > x && px < x + width && py > y && py < y + height){
			return entity;
		}
	}
	
	return null;
}

/**
	Init when the browser is ready.
*/
window.addEventListener('DOMContentLoaded', function(e){
	getJSONFile('GET', '/data/maps/starting.json', function(o){
		try{
			map = JSON.parse(o);
		}
		catch(e){
			console.error(e);
		}
	},
	function(e){
		console.error('Failed to fetch map file', e);
	});
	
	var canvas = document.getElementById('canvas');
	canvas.style.width = '100%';
	canvas.style.minWidth = '800px';
	canvas.style.height = canvas.clientWidth;
	canvas.width = canvas.clientWidth;
	canvas.height = canvas.clientHeight;
	canvas.onmousedown = handle_mouse_down;
	
	var context = canvas.getContext('2d');
	context.imageSmoothingEnabled = false;
	
	request_image('img/colored_tilemap_packed.png', function(i){
		tilemap = i;
	});
	
	socket = new WebSocket("ws://192.168.1.3:4444");
	socket.onopen = function(e){
		console.log('Connection established');
		
		var key;
		if (window.location.hash){
			key = window.location.hash;
		}
		else {
			key = 1000 + Math.floor(Math.random() * 1000000);
			window.location.hash = key;
			key = '#' + key;
		}
		
		socket.send(JSON.stringify({
			type : 'key',
			value : key
		}));
	};
	
	socket.onmessage = function(e){
		console.log('Response: ' + e.data);
		
		try{
			handle_packet(JSON.parse(e.data));
		}
		catch(f){
			console.error('Error handling packet', e.data, f);
		}
	};
	
	socket.onclose = function(e){
		console.log(e.wasClean ? 'Connection closed cleanly: ' + e.code + ' ' + e.reason : 'Connection died');
	};
	
	socket.onerror = function(e){
		console.error(e);
	}
	
	document.body.addEventListener('mousemove', e => {
		mouse_x = e.layerX;
		mouse_y = e.layerY;
	});
	
	document.body.addEventListener('keydown', e => {
		handle_key(e.key);
	});
	
	// Start rendering frames and updating at 30fps
	frame_interval = setInterval(bake_frame(context), 34);
});

/**
	Shorthand so we can send messages manually from console.
*/
function send(v){
	if (v.constructor.toString().indexOf('Array') > -1 ){
		for (var i = 0; i < v.length; i ++){
			setTimeout((function(line){ return function(){ socket.send(JSON.stringify(line)); }; })(v[i]), i * 100);
		}
	}
	else{
		socket.send(JSON.stringify(v));
	}
}