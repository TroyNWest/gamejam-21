import 'phaser';
import MainStage from "./scenes/game";

const config = {
    type: Phaser.AUTO,
    parent: "phaser-example",
    width: 1280,
    height: 780,
    scene: [
        MainStage
    ]
};

const game = new Phaser.Game(config);
