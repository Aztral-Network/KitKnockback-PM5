<?php

declare(strict_types=1);

namespace kitkb\kits;

use kitkb\KitKb;
use kitkb\KitKbListener;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Kit
{
    private string $name;
    private array $items;
    private array $armor;
    private KbInfo $kbInfo;
    private array $effects;

    public function __construct(string $name, array $items = [], array $armor = [], array $effects = [], ?KbInfo $info = null)
    {
        $this->name = $name;
        $this->items = $items;
        $this->armor = $armor;
        $this->effects = $effects;
        $this->kbInfo = ($info !== null) ? $info : new KbInfo();
    }

    public function getKbInfo(): KbInfo {
        return $this->kbInfo;
    }

    public function setKbInfo(KbInfo $kbInfo): void {
        $this->kbInfo = $kbInfo;
    }

    public function getName(): string {
        return $this->name;
    }

    public function giveTo(Player $player, bool $message = true) {

        $playerName = $player->getName();
        KitKbListener::removePlayerKit($playerName);
        KitKbListener::setPlayerKit($playerName, $this->name);

        $inventory = $player->getInventory();
        $armorInventory = $player->getArmorInventory();

        foreach($this->items as $slot => $item) {
            if($item instanceof Item) {
                $inventory->setItem((int)$slot, $item);
            }
        }

        foreach($this->armor as $slot => $item) {
            if($item instanceof Item) {
                $armorInventory->setItem((int)$slot, $item);
            }
        }

        foreach($this->effects as $effect) {
            if($effect !== null) {
                $player->getEffects()->add($effect);
            }
        }

        KitKbListener::setPlayerKb($player->getName(), $this->kbInfo);

        if($message) {
            // Message sending is currently disabled
        }
    }

    public function toArray(): array {
        $items = [];
        foreach($this->items as $slot => $item) {
            $str = KitKb::itemToStr($item);
            $items[$slot] = $str;
        }

        $armor = [];
        foreach($this->armor as $slot => $item) {
            if ($item instanceof Item) {
                $key = KitKb::getArmorStr((int)$slot);
                $value = KitKb::itemToStr($item);
                $armor[$key] = $value;
            }
        }

        $effects = [];
        foreach($this->effects as $effect) {
            if($effect instanceof EffectInstance) {
                $value = KitKb::effectToStr($effect);
                $effects[] = $value;
            }
        }

        $kb = $this->kbInfo->toArray();

        return ['items' => $items, 'armor' => $armor, 'effects' => $effects, 'kb' => $kb];
    }
}
