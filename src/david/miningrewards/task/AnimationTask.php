<?php

namespace david\miningrewards\task;

use david\miningrewards\Loader;
use pocketmine\entity\object\ItemEntity;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\scheduler\Task;

class AnimationTask extends Task {

    /** @var ItemEntity */
    private $item;

    /**
     * AnimationTask constructor.
     *
     * @param ItemEntity $item
     */
    public function __construct(ItemEntity $item) {
        $this->item = $item;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $amount = mt_rand(Loader::getInstance()->getCountMin(), Loader::getInstance()->getCountMax());
        $rewards = Loader::getInstance()->getRewards();
        for($i = 0; $i < $amount; $i++) {
            $this->item->getLevel()->dropItem($this->item, $rewards[array_rand($rewards)]);
        }
        $this->item->getLevel()->addParticle(new HugeExplodeSeedParticle($this->item));
        $this->item->getLevel()->broadcastLevelSoundEvent($this->item, LevelSoundEventPacket::SOUND_EXPLODE);
        $this->item->flagForDespawn();
    }
}