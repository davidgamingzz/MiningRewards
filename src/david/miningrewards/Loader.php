<?php

namespace david\miningrewards;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase {

    /** @var EventListener */
    public $listener;

    /** @var Item[] */
    private $rewards;

    /** @var int */
    private $countMin;

    /** @var int */
    private $countMax;

    /** @var int */
    private $chance;

    /** @var self */
    private static $instance;

    public function onLoad() {
        self::$instance = $this;
    }

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->parseConfig();
        $this->listener = new EventListener($this);
    }

    /**
     * @throws PluginException
     */
    public function parseConfig() {
        $elements = $this->getConfig()->getAll();
        if((!isset($elements["rewards"])) or (!isset($elements["reward-count-min"])) or
            (!isset($elements["reward-count-max"]) or (!isset($elements["chance"])))) {
            throw new PluginException("Error while parsing through configuration file! Couldn't find the required elements!");
        }
        $rewards = [];
        $count = 0;
        foreach($elements["rewards"] as $reward) {
            ++$count;
            $reward = explode(":", $reward);
            if(!isset($reward[2])) {
                throw new PluginException("Error while parsing through rewards! Error found in reward #$count");
            }
            $item = Item::get((int)$reward[0], (int)$reward[1], (int)$reward[2]);
            if(isset($reward[3])) {
                $item->setCustomName(str_replace("&", TextFormat::ESCAPE, (string)$reward[3]));
            }
            if(isset($reward[4])) {
                $enchantments = explode(",", (string)$reward[4]);
                foreach($enchantments as $enchantment) {
                    $parts = explode("/", $enchantment);
                    if(!isset($parts[1])) {
                        throw new PluginException("Error while parsing through rewards! Error found in reward #$count");
                    }
                    $enchantment = Enchantment::getEnchantment((int)$parts[0]);
                    if($enchantment === null) {
                        throw new PluginException("Error while parsing through rewards! Unknown enchant id: $parts[0]. Error found in reward #$count");
                    }
                    $level = (int)$parts[1];
                    if($level < 0) {
                        throw new PluginException("Error while parsing through rewards! Invalid enchant level: $level. Error found in reward #$count");
                    }
                    $item->addEnchantment(new EnchantmentInstance($enchantment, $level));
                }
            }
            $rewards[] = $item;
        }
        $this->rewards = $rewards;
        $this->countMin = (int)$elements["reward-count-min"] > 0 ? (int)$elements["reward-count-min"] : 1;
        $this->countMax = (int)$elements["reward-count-max"] > $this->countMin ? (int)$elements["reward-count-max"] : 5;
        $this->chance = (int)$elements["chance"] > 0 ? (int)$elements["chance"] : 100;
    }

    /**
     * @return Loader
     */
    public static function getInstance(): self {
        return self::$instance;
    }

    /**
     * @return Item[]
     */
    public function getRewards(): array {
        return $this->rewards;
    }

    /**
     * @return int
     */
    public function getCountMin(): int {
        return $this->countMin;
    }

    /**
     * @return int
     */
    public function getCountMax(): int {
        return $this->countMax;
    }

    /**
     * @return int
     */
    public function getChance(): int {
        return $this->chance;
    }
}