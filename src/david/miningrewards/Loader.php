<?php

namespace david\miningrewards;

use david\miningrewards\item\Reward;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase {
    /** @var EventListener */
    public EventListener $listener;

    /** @var Item[] */
    private array $rewards;

    /** @var int */
    private int $countMin;

    /** @var int */
    private int $countMax;

    /** @var int */
    private int $chance;

    /** @var int */
    private int $animationTickRate;

    /** @var self */
    private static self $instance;

    /** @var string */
    private static string $prefix;

    /** @var string[] */
    private static array $titles;

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->parseConfig();

        $this->listener = new EventListener($this);
    }

    /**
     * @throws PluginException
     */
    public function parseConfig(): void {
        $elements = $this->getConfig()->getAll();
        if((!isset($elements["rewards"])) or (!isset($elements["reward-count-min"])) or
            (!isset($elements["reward-count-max"])) or (!isset($elements["chance"])) or (!isset($elements["prefix"])) or
            (!isset($elements["mining-reward-id"])) or (!isset($elements["titles"]))) {
            throw new PluginException("Error while parsing through configuration file! Couldn't find the required elements!");
        }
        if(is_null(StringToItemParser::getInstance()->parse($elements["mining-reward-id"]))) {
            throw new PluginException("Error while parsing through configuration file! Invalid item identifier in mining-reward-id!");
        }
        $rewards = [];
        foreach($elements["rewards"] as $id => $reward) {
            if($reward["type"] === "item") {
                if((!isset($reward["id"])) or (!is_string($reward["id"]))) {
                    throw new PluginException("Error while parsing through rewards! Invalid item identifier in reward named $id!");
                }
                $item = StringToItemParser::getInstance()->parse($reward["id"]);
                if(is_null($item)) {
                    throw new PluginException("Error while parsing through rewards! Item with named $id could not be found!");
                }
                if(isset($reward["customName"]) and $reward["customName"] !== "Default") {
                    $item->setCustomName(str_replace("&", TextFormat::ESCAPE, (string)$reward["customName"]));
                }
                if(isset($reward["enchantments"])) {
                    foreach($reward["enchantments"] as $enchantment) {
                        $parts = explode(":", $enchantment);
                        if(!isset($parts[1])) {
                            throw new PluginException("Error while parsing through rewards! Invalid enchantment found in reward named $id!");
                        }
                        $enchantment = StringToEnchantmentParser::getInstance()->parse($parts[0]);
                        if($enchantment === null) {
                            throw new PluginException("Error while parsing through rewards! Unknown enchantment id $parts[0] in reward named $id!");
                        }
                        $level = (int)$parts[1];
                        if($level < 0) {
                            throw new PluginException("Error while parsing through rewards! Invalid enchantment level $level in reward named $id.");
                        }
                        $item->addEnchantment(new EnchantmentInstance($enchantment, $level));
                    }
                }
                $rewards[] = $item;
                continue;
            }
            if($reward["type"] === "command") {
                if(!isset($reward["command"])) {
                    throw new PluginException("Error while parsing through rewards! Invalid command in reward named $id!");
                }
                $command = $reward["command"];
                if(isset($reward["message"])) {
                    $command = $command . ":" . $reward["message"];
                }
                $rewards[] = (string)$command;
                continue;
            }
            throw new PluginException("Error while parsing through rewards! Invalid type in reward named $id!");
        }
        $this->rewards = $rewards;
        $this->countMin = (int)$elements["reward-count-min"] > 0 ? (int)$elements["reward-count-min"] : 1;
        $this->countMax = (int)$elements["reward-count-max"] > $this->countMin ? (int)$elements["reward-count-max"] : 5;
        $this->chance = (int)$elements["chance"] > 0 ? (int)$elements["chance"] : 100;
        $this->animationTickRate = (int)$elements["lengthOfAnimation"] > 0 ? (int)$elements["lengthOfAnimation"] : 20;
        self::$prefix = str_replace("&", TextFormat::ESCAPE, (string)$elements["prefix"]);
        self::$titles = $elements["titles"];
    }

    /**
     * @return Loader
     */
    public static function getInstance(): self {
        return self::$instance;
    }

    /**
     * @return string
     */
    public static function getPrefix(): string {
        return self::$prefix;
    }

    /**
     * @return string[]
     */
    public static function getTitles(): array {
        return self::$titles;
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

    /**
     * @return int
     */
    public function getAnimationTickRate(): int {
        return $this->animationTickRate;
    }
}