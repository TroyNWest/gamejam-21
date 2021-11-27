import Phaser from "phaser";
import MainStage from "./scenes/game"

const config = {
    type: Phaser.AUTO,
    parent: "phaser-example",
    width: 780,
    height: 780,
    scene: [
        MainStage
    ]
};

const game = new Phaser.Game(config);

