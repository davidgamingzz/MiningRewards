<?php

namespace david\miningrewards;

use david\miningrewards\task\TickTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\sound\BlazeShootSound;

function chance($chance): bool {
    return mt_rand(1, 100) <= $chance;
}

class EventListener implements Listener {
    /** @var Loader */
    private Loader $plugin;

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
     * @param BlockBreakEvent $event
     *
     * @priority MONITOR
     */
    public function onBlockBreak(BlockBreakEvent $event): void {
        if($event->isCancelled()) {
            return;
        }
        if(in_array($event->getItem()->getVanillaName(), array_map('strtolower', Loader::getInstance()->getConfig()->get("blacklist", [])))) {
            return;
        }
        if(chance($this->plugin->getChance())) {
            $player = $event->getPlayer();
            $level = $player->getWorld();
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
            $player->getInventory()->canAddItem($item) ? $player->getInventory()->addItem($item) : $level->dropItem($player->getPosition(), $item);
            $level->addParticle($player->getPosition(), new HugeExplodeSeedParticle(), [$player]);
            $level->addSound($player->getPosition(), new BlazeShootSound(), [$player]);
            $titles = Loader::getTitles();
            $player->sendTitle(TextFormat::BOLD . TextFormat::AQUA . $titles[array_rand($titles)],
                TextFormat::GRAY . "You have found a reward from mining!");
        }
    }

    /**
     * @param PlayerItemUseEvent $event
     * @return void
     */
    public function onItemClick(PlayerItemUseEvent $event): void {
        if($event->isCancelled()) {
            return;
        }
        $player = $event->getPlayer();
        $directionVector = $player->getDirectionVector();
        $item = $event->getItem();
        $miningItem = StringToItemParser::getInstance()->parse(Loader::getInstance()->getConfig()->get("mining-reward-id", "ender_eye"));

        if($item->getTypeId() == $item->getTypeId()) {
            $tag = $item->getNamedTag();
            if($tag->getTag("reward") === null) {
                return;
            }
            if($tag->getInt("reward") !== 1) {
                return;
            }
            $itemEntity = $player->getWorld()->dropItem($player->getPosition()->add(0, 1, 0), $miningItem, $directionVector->multiply(0.5), 1000);
            $player->sendMessage(Loader::getPrefix() . TextFormat::GREEN . "Opening reward...!");

            $item = $player->getInventory()->getItemInHand();
            $item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);
            Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new TickTask($player, $itemEntity, Loader::getInstance()->getAnimationTickRate()), 5);
        }
    }
}