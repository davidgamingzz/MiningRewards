<?php

namespace david\miningrewards;

use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;

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
        foreach($elements["rewards"] as $reward) {
            $reward = explode(":", $reward);
            if(!isset($reward[2])) {
                throw new PluginException("Error while parsing through rewards! Check for any errors!");
            }
            $rewards[] = Item::get((int)$reward[0], (int)$reward[1], (int)$reward[2]);
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