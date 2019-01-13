<?php

namespace david\miningrewards\task;

use david\miningrewards\Loader;
use pocketmine\level\Level;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\Position;
use pocketmine\level\sound\ClickSound;
use pocketmine\plugin\PluginException;
use pocketmine\scheduler\Task;

class TickTask extends Task {

    /** @var int */
    private $limit;

    /** @var Position */
    private $position;

    /** @var int */
    private $runs = 0;

    /**
     * TickTask constructor.
     *
     * @param Position $position
     * @param int $limit
     */
    public function __construct(Position $position, int $limit) {
        $this->position = $position;
        $this->limit = $limit;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $this->runs++;
        if($this->runs === $this->limit) {
            Loader::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
        $level = $this->position->getLevel();
        if(!$level instanceof Level) {
            Loader::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            throw new PluginException("Task can't be executed in an invalid level.");
        }
        $level->addSound(new ClickSound($this->position));
        for($i = 0; $i < 4; $i++) {
            $level->addParticle(new SmokeParticle($this->position->add(0, 0.15, 0)));
            $level->addParticle(new FlameParticle($this->position->add(0, 0.15, 0)));
        }
    }
}