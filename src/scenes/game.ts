import 'phaser';

export default class MainStage extends Phaser.Scene {
    constructor() {
        super('mainstage');
    }

    preload() {
        this.load.image('tiles', 'assets/tiles/colored_tilemap_packed.png');
        this.load.tilemapTiledJSON('MainStage', 'assets/tilemap/starting.json')
    }

    create() {
        const map = this.make.tilemap({ key: 'MainStage' });

        const tiles = map.addTilesetImage('colored_tilemap_packed', 'tiles');

        const layer = map.createLayer('starting-map', tiles, 0, 0);
        // this.backgroundLayer = map.createStaticLayer('Background', this.tiles, 0, 0)

        const rt = this.add.renderTexture(0, 0, 800, 600);
    }
}

