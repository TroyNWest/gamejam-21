import 'phaser';

export default class Demo extends Phaser.Scene {
    constructor() {
        super('demo');
    }

    preload() {
        this.load.atlas('rogue_atlas', 'assets/tiles/colored_tilemap_packed.png', 'assets/tiles/rogue_atlas.json')
        // this.load.spritesheet('rogue', 'assets/tilemap/colored_tilemap_packed.png', { frameWidth: 8, frameHeight: 8 })
    }

    create() {
        console.log("before atlas!");

        const atlas = this.add.sprite(400, 300, 'rogue_atlas', 'wall_door_top')

        // const sprite = this.add.sprite(400, 300, 'rogue', 4);
        // sprite.frame = 1;
        atlas.setScale(3, 3);



        // this.add.shader('RGB Shift Field', 0, 0, 800, 600).setOrigin(0);

        // this.add.shader('Plasma', 0, 412, 800, 172).setOrigin(0);

        // this.add.image(400, 300, 'libs');

        // const logo = this.add.image(400, 70, 'logo');

        // this.tweens.add({
        //     targets: logo,
        //     y: 350,
        //     duration: 1500,
        //     ease: 'Sine.inOut',
        //     yoyo: true,
        //     repeat: -1
        // })
    }
}

const config = {
    type: Phaser.AUTO,
    backgroundColor: '#125555',
    width: 800,
    height: 600,
    scene: Demo
};

const game = new Phaser.Game(config);
