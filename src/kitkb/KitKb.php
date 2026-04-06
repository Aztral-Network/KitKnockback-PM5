<?php

declare(strict_types=1);

namespace kitkb;

use kitkb\commands\CreateKitCommand;
use kitkb\commands\DeleteKitCommand;
use kitkb\commands\KitClearCommand;
use kitkb\commands\KitCommand;
use kitkb\commands\ListKitCommand;
use kitkb\commands\KbInfoCommand;
use kitkb\commands\RefreshKitsCommand;
use kitkb\commands\knockback\XKBCommand;
use kitkb\commands\knockback\YKBCommand;
use kitkb\commands\knockback\KBSpeedCommand;
use kitkb\kits\KitHandler;
use kitkb\kits\KbInfo;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class KitKb extends PluginBase
{

    const KB_Y = "y";
    const KB_X = "x";
    const KB_SPEED = "speed";

    const ARMOR_INDEXES = [
        'helmet',
        'chestplate',
        'leggings',
        'boots'
    ];

    /** @var string */
    private $dataFolder = "";

    /** @var KitHandler */
    private static $kitHandler;

    public function onEnable(): void
    {
        $this->initDataFolder();

        self::$kitHandler = new KitHandler($this);

        $this->registerCommands();
        new KitKbListener($this);
    }

    private function initDataFolder() {
        $this->dataFolder = $this->getDataFolder();
        if(!is_dir($this->dataFolder)) {
            mkdir($this->dataFolder);
        }
    }

    public static function getKitHandler() {
        return self::$kitHandler;
    }

    private function registerCommands()
    {
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->register('createkit', new CreateKitCommand($this));
        $commandMap->register('givekit', new KitCommand($this));
        $commandMap->register('clearkit', new KitClearCommand($this));
        $commandMap->register('deletekit', new DeleteKitCommand($this));
        $commandMap->register('listkits', new ListKitCommand($this));
        $commandMap->register('xkb', new XKBCommand());
        $commandMap->register('ykb', new YKBCommand());
        $commandMap->register('kbspeed', new KBSpeedCommand());
        $commandMap->register('kbinfo', new KbInfoCommand());
        $commandMap->register('kitsrefresh', new RefreshKitsCommand($this));
    }

    public static function inventoryToArray($player) {
        $inventory = $player->getInventory();

        return [
            'items' => $inventory->getContents(),
            'armor' => $player->getArmorInventory()->getContents()
        ];
    }

    public static function itemToStr(\pocketmine\item\Item $item) {
        $enchants = $item->getEnchantments();

        $size = count($enchants);

        $enchantStr = '';

        if($size > 0) {
            $size--;
            $count = 0;
            foreach($enchants as $enchant) {
                $comma = $count === $size ? '' : ',';
                $str = "{$enchant->getType()->getName()}:{$enchant->getLevel()}";
                $enchantStr .= $str . $comma;
            }
        }

        $itemName = \pocketmine\item\StringToItemParser::getInstance()->lookupAliases($item)[0] ?? "air";

        return "{$itemName}:{$item->getCount()}:0" . (($size > 0) ? "-$enchantStr" : '');
    }

    public static function strToItem(string $string) {
        $split = explode('-', $string);

        $itemPortion = strval($split[0]);

        $enchants = [];

        if(isset($split[1])) {
            $enchantPortion = strval($split[1]);
            $enchantsSplit = explode(',', $enchantPortion);
            foreach($enchantsSplit as $e) {
                $enchantData = explode(':', strval($e));
                if(isset($enchantData[0], $enchantData[1])) {
                    $enchantID = strval($enchantData[0]);
                    $level = intval($enchantData[1]);
                    $enchantType = self::getEnchantmentById($enchantID);
                    if ($enchantType !== null) {
                        $enchant = new EnchantmentInstance($enchantType, $level);
                        $enchants[] = $enchant;
                    }
                }
            }
        }

        $itemData = explode(':', $itemPortion);

        $item = null;

        if(isset($itemData[0])) {

            $id = $itemData[0];
            $count = 1;
            $meta = 0;
            if(isset($itemData[1])) {
                $count = intval($itemData[1]);
                if(isset($itemData[2])) {
                    $meta = intval($itemData[2]);
                }
            }

            try {
                $itemString = "$id";
                $item = \pocketmine\item\StringToItemParser::getInstance()->parse($itemString);
                if ($item !== null) {
                    $item->setCount($count);
                } else {
                    $itemString = "$id:$count";
                    if($meta > 0) {
                        $itemString = "$id:$meta:$count";
                    }
                    $item = LegacyStringToItemParser::getInstance()->parse($itemString);
                }
            } catch (\Exception $e) {
                $item = null;
            }
            
            if($item === null) {
                $item = VanillaItems::AIR();
            } else {
                if(count($enchants) > 0) {
                    foreach($enchants as $e) {
                        $item->addEnchantment($e);
                    }
                }
            }
        }

        return $item;
    }

    public static function effectToStr(EffectInstance $effect) {
        return EffectIdMap::getInstance()->toId($effect->getType()) . ":{$effect->getAmplifier()}:{$effect->getDuration()}";
    }

    public static function strToEffect(string $string) {
        $effectData = explode(':', $string);
        if(isset($effectData[0])) {
            $id = intval($effectData[0]);
            $amplifier = 0;
            $duration = self::minutesToTicks(5);
            if(isset($effectData[1])) {
                $amplifier = intval($effectData[1]);
                if(isset($effectData[2])) {
                    $duration = intval($effectData[2]);
                }
            }
            $effectType = EffectIdMap::getInstance()->fromId($id);
            if($effectType !== null) {
                return new EffectInstance($effectType, $duration, $amplifier);
            }
        }
        return null;
    }

    public static function minutesToTicks(int $minutes) {
        return $minutes * 1200;
    }

    public static function secondsToTicks(int $seconds) {
        return $seconds * 20;
    }

    public static function getArmorStr($index) {

        $arr = self::ARMOR_INDEXES;

        if(is_string($index)) {
            $arr = array_flip($arr);
        }

        $index = (is_int($index) ? $index % 4 : $index);
        return $arr[$index];
    }

    public static function getConsoleMsg() : string {
        return TextFormat::RED . 'Use in game.';
    }

    public static function getEnchantmentById(string $id): ?\pocketmine\item\enchantment\Enchantment {
        $map = [
            'protection' => VanillaEnchantments::PROTECTION(),
            'fire_protection' => VanillaEnchantments::FIRE_PROTECTION(),
            'feather_falling' => VanillaEnchantments::FEATHER_FALLING(),
            'blast_protection' => VanillaEnchantments::BLAST_PROTECTION(),
            'projectile_protection' => VanillaEnchantments::PROJECTILE_PROTECTION(),
            'thorns' => VanillaEnchantments::THORNS(),
            'sharpness' => VanillaEnchantments::SHARPNESS(),
            'smite' => VanillaEnchantments::SMITE(),
            'bane_of_arthropods' => VanillaEnchantments::BANE_OF_ARTHROPODS(),
            'knockback' => VanillaEnchantments::KNOCKBACK(),
            'fire_aspect' => VanillaEnchantments::FIRE_ASPECT(),
            'looting' => VanillaEnchantments::LOOTING(),
            'efficiency' => VanillaEnchantments::EFFICIENCY(),
            'silk_touch' => VanillaEnchantments::SILK_TOUCH(),
            'unbreaking' => VanillaEnchantments::UNBREAKING(),
            'fortune' => VanillaEnchantments::FORTUNE(),
            'power' => VanillaEnchantments::POWER(),
            'punch' => VanillaEnchantments::PUNCH(),
            'flame' => VanillaEnchantments::FLAME(),
            'infinity' => VanillaEnchantments::INFINITY(),
            'mending' => VanillaEnchantments::MENDING(),
            'vanishing' => VanillaEnchantments::VANISHING(),
        ];
        return $map[$id] ?? null;
    }
}
