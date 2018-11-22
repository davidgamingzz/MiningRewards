<?php

namespace david\miningrewards\task;

use david\miningrewards\Loader;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\Position;
use pocketmine\level\sound\ClickSound;
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
        $this->position->getLevel()->addSound(new ClickSound($this->position));
        for($i = 0; $i < 4; $i++) {
            $this->position->getLevel()->addParticle(new SmokeParticle($this->position->add(0, 0.15, 0)));
            $this->position->getLevel()->addParticle(new FlameParticle($this->position->add(0, 0.15, 0)));
        }
    }
}