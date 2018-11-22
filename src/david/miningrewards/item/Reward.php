<?php

namespace david\miningrewards\item;

use david\miningrewards\Loader;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

class Reward extends Item {

    const TAG = "Reward";

    /**
     * Reward constructor.
     */
    public function __construct() {
        $lore = [];
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::YELLOW . "Reward Min: " . Loader::getInstance()->getCountMin();
        $lore[] = TextFormat::RESET . TextFormat::YELLOW . "Reward Max: " . Loader::getInstance()->getCountMax();
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Click to open reward.";
        $this->setLore($lore);
        $this->setCustomName(TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "Reward");
        $this->setNamedTagEntry(new CompoundTag(self::TAG));
        parent::__construct(self::ENDER_EYE);
    }
}