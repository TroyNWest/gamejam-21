
export default class MainStage extends Phaser.Scene {
    constructor() {
        super({
            key: 'MainStage'
        });

        this.mapsRef = {
            1: { name: "MainStage", id: 1, layer: "starting-map" }
        }

        // this.socket = new WebSocket("ws://192.168.254.81:4444");

        this.startingMap = "MainStage"
    }

    preload() {

        this.load.image('tiles', 'src/assets/tilemap/tiles/colored_tilemap_packed.png');
        this.load.tilemapTiledJSON('MainStage', 'src/assets/tilemap/starting.json')
        this.load.atlas('atlas', 'src/assets/tilemap/tiles/colored_tilemap_packed.png', 'src/assets/colored_tilemap_packed_atlas.json');
    }

    create() {
        let loadMap = false;
        const socket = new WebSocket("ws://192.168.254.81:4444");
        socket.onopen = function (e) {
            socket.send('{"type": "poop", "weight": 5, "toiletPaper" : -5, "poopSmith": {"pleased":"false"}}');
        }.bind(this);

        // Try to get which map to load
        socket.onmessage = function (event) {
            // console.log(newEvent.type)
            const newEvent = JSON.parse(event.data)
            if (newEvent.type === "map") {
                const { map: { id } } = newEvent
                const mapRef = this.mapsRef[id]
                const map = this.make.tilemap({ key: mapRef.name });

                const tiles = map.addTilesetImage('colored_tilemap_packed', 'tiles');

                const layer = map.createLayer(mapRef.layer, tiles, 0, 0);
                layer.setScale(2, 2)

                const rt = this.add.renderTexture(0, 0, 600, 600);
            }

            if (newEvent.type === "entity_create") {
                const { data: { sprite, position: [pos_x, pos_y] } } = newEvent;
                //this.add.tileSprite(pos_x, pos_y, 8, 8, sprite);
                this.add.sprite(pos_x, pos_y, "tiles", sprite)

            }
        }.bind(this);

        socket.onclose = function (event) {
            if (event.wasClean) {
                alert(`[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`);
            } else {
                // e.g. server process killed or network down
                // event.code is usually 1006 in this case
                alert('[close] Connection died');
            }
        };

        socket.onerror = function (error) {
            // alert(`[error] ${error.message}`);
        };


    }

    update() {

    }
}

