<?php

namespace david\miningrewards;

use david\miningrewards\item\Reward;
use david\miningrewards\task\AnimationTask;
use david\miningrewards\task\TickTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;

class EventListener implements Listener, Messages {

    /** @var Loader */
    private $plugin;

    /**
     * EventListener constructor.
     *
     * @param Loader $plugin
     */
    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event) {
        $item = $event->getItem();
        if(!$item->getId() === Item::ENDER_EYE) {
            return;
        }
        if($item->getNamedTagEntry(Reward::TAG) === null) {
            return;
        }
        $player = $event->getPlayer();
        $player->sendMessage(TextFormat::GREEN . "Opening reward...!");
        $player->getInventory()->removeItem($item);
        $item = $player->getLevel()->dropItem($player->getSide($player->getDirection(), 1), $item, new Vector3(), 1000);
        $this->plugin->getScheduler()->scheduleRepeatingTask(new TickTask($item, 20), 5);
        $this->plugin->getScheduler()->scheduleDelayedTask(new AnimationTask($item), 100);
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event) {
        if($event->isCancelled()) {
            return;
        }
        if(mt_rand(1, $this->plugin->getChance()) === mt_rand(1, $this->plugin->getChance())) {
            $player = $event->getPlayer();
            $level = $player->getLevel();
            $level->dropItem($player, new Reward());
            $level->addParticle(new HugeExplodeSeedParticle($player));
            $level->addSound(new BlazeShootSound($player));
            $player->addTitle(TextFormat::BOLD . TextFormat::AQUA . self::MESSAGES[array_rand(self::MESSAGES)],
                TextFormat::GRAY . "You have found a reward from mining!");
        }
    }
}