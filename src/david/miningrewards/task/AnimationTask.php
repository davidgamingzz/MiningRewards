<?php

namespace david\miningrewards\task;

use david\miningrewards\Loader;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\sound\ExplodeSound;

class AnimationTask extends Task {
    /** @var Player */
    private Player $player;

    /** @var ItemEntity */
    private ItemEntity $item;

    /**
     * AnimationTask constructor.
     *
     * @param Player $player
     * @param ItemEntity $item
     */
    public function __construct(Player $player, ItemEntity $item) {
        $this->player= $player;
        $this->item = $item;
    }

    public function onRun(): void {
        $amount = mt_rand(Loader::getInstance()->getCountMin(), Loader::getInstance()->getCountMax());
        $rewards = Loader::getInstance()->getRewards();
        for($i = 0; $i < $amount; $i++) {
            $reward = $rewards[array_rand($rewards)];
            if($reward instanceof Item) {
                $this->item->getWorld()->dropItem($this->item->getPosition(), $reward);
                $this->player->getInventory()->canAddItem($reward) ? $this->player->getInventory()->addItem($reward) : $this->item->getWorld()->dropItem($this->item->getPosition(), $reward);
                continue;
            }
            $reward = explode(":", $reward);
            $server = Server::getInstance();
            Loader::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender($server, $server->getLanguage()),
                str_replace("{player}", $this->player->getName(), $reward[0]));
            if(isset($reward[1])) {
                $this->player->sendMessage(str_replace("&", TextFormat::ESCAPE, $reward[1]));
            }
        }
        $this->item->getWorld()->addParticle($this->item->getPosition(), new HugeExplodeSeedParticle());
        $this->item->getWorld()->addSound($this->item->getPosition(), new ExplodeSound());
        $this->item->flagForDespawn();
    }
}