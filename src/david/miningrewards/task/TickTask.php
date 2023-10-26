<?php

namespace david\miningrewards\task;

use david\miningrewards\Loader;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\StringToItemParser;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\sound\ClickSound;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class TickTask extends Task {
    /** @var int */
    private int $limit;

    /** @var Player */
    private Player $owner;

    /** @var ItemEntity */
    private ItemEntity $entity;

    /** @var int */
    private int $runs = 0;

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

    public function onRun(): void {
        $this->runs++;
        $scheduler = Loader::getInstance()->getScheduler();
        if($this->entity->isClosed()) {
            $item = StringToItemParser::getInstance()->parse(Loader::getInstance()->getConfig()->get("mining-reward-id", "diamond"));
            $tag = $item->getNamedTag();
            $tag->setInt("reward", 1);
            $lore = [];
            $lore[] = TextFormat::RESET . TextFormat::YELLOW . "Reward Min: " . Loader::getInstance()->getCountMin();
            $lore[] = TextFormat::RESET . TextFormat::YELLOW . "Reward Max: " . Loader::getInstance()->getCountMax();
            $lore[] = "";
            $lore[] = TextFormat::RESET . TextFormat::GRAY . "Click to open reward.";
            $item->setLore($lore);
            $item->setCustomName(TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "Reward");
            $this->owner->getInventory()->canAddItem($item) ? $this->owner->getInventory()->addItem($item) : $this->owner->getWorld()->dropItem($this->owner->getPosition(), $item, $this->owner->getDirectionVector()->multiply(0.5), 1000);
            $this->owner->sendMessage(Loader::getPrefix() . TextFormat::RED . "Error occurred when using reward! It has been returned to your inventory!");
            $this->getHandler()->cancel();
            return;
        }
        if($this->owner->isOnline() === false) {
            $this->entity->close();
            $this->getHandler()->cancel();
            return;
        }
        if($this->runs === $this->limit) {
            $this->getHandler()->cancel();
            $scheduler->scheduleTask(new AnimationTask($this->owner, $this->entity));
            return;
        }
        $level = $this->entity->getWorld();
        $level->addSound($this->entity->getPosition(), new ClickSound());
        $position = $this->entity->getPosition()->add(0, 0.15, 0);
        for($i = 0; $i < 4; $i++) {
            $level->addParticle($position, new SmokeParticle());
            $level->addParticle($position, new FlameParticle());
        }
    }
}