<?php

namespace david\miningrewards\task;

use david\miningrewards\Loader;
use pocketmine\entity\object\ItemEntity;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\sound\ClickSound;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class TickTask extends Task {

    /** @var int */
    private $limit;

    /** @var Player */
    private $owner;

    /** @var ItemEntity */
    private $entity;

    /** @var int */
    private $runs = 0;

    /**
     * TickTask constructor.
     *
     * @param Player $owner
     * @param ItemEntity $entity
     * @param int $limit
     */
    public function __construct(Player $owner, ItemEntity $entity, int $limit) {
        $this->owner = $owner;
        $this->entity = $entity;
        $this->limit = $limit;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $this->runs++;
        $scheduler = Loader::getInstance()->getScheduler();
        if($this->entity->isClosed()) {
            $this->owner->getInventory()->addItem($this->entity->getItem());
            $this->owner->sendMessage(Loader::getPrefix() . TextFormat::RED . "Error occurred when using reward! It has been returned to your inventory!");
            $scheduler->cancelTask($this->getTaskId());
            return;
        }
        if($this->owner->isOnline() === false) {
            $this->entity->close();
            $scheduler->cancelTask($this->getTaskId());
            return;
        }
        if($this->runs === $this->limit) {
            $scheduler->cancelTask($this->getTaskId());
            $scheduler->scheduleTask(new AnimationTask($this->owner, $this->entity));
            return;
        }
        $level = $this->entity->getLevel();
        $level->addSound(new ClickSound($this->entity->asPosition()));
        $position = $this->entity->asPosition()->add(0, 0.15, 0);
        for($i = 0; $i < 4; $i++) {
            $level->addParticle(new SmokeParticle($position));
            $level->addParticle(new FlameParticle($position));
        }
    }
}