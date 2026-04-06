<?php

declare(strict_types=1);

namespace kitkb;

use kitkb\Player\KitKbPlayer;
use kitkb\kits\KbInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;

class KitKbListener implements Listener
{
    /** @var KitKb */
    private $kitKb;

    private static array $playerKb = [];
    private static array $playerKits = [];
    private static array $playerLastAttack = [];

    public function __construct(KitKb $kb)
    {
        $this->kitKb = $kb;
        $kb->getServer()->getPluginManager()->registerEvents($this, $kb);
    }

    public function onPlayerCreation(PlayerCreationEvent $event): void {
        $event->setPlayerClass(KitKbPlayer::class);
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        self::removePlayerKb($player->getName());
        self::removePlayerKit($player->getName());
        self::removePlayerLastAttack($player->getName());
        if($player instanceof KitKbPlayer) {
            $player->clearKit();
        }
    }

    public function onDamage(EntityDamageByEntityEvent $event): void {
        $damager = $event->getDamager();
        $entity = $event->getEntity();

        if (!$damager instanceof Player || !$entity instanceof Player) {
            return;
        }

        $damagerName = $damager->getName();
        $entityName = $entity->getName();
        
        // Handle custom KB for the entity (victim) if damager has a kit
        if (isset(self::$playerKb[$damagerName])) {
            $kb = self::$playerKb[$damagerName];
            
            $horizontal = $kb->getXKb();
            $vertical = $kb->getYKb();
            $event->setKnockBack($horizontal, $vertical);
        }

        // Handle KB speed (attackTime/hit cooldown) for the entity (victim) if THEY have a kit
        if (isset(self::$playerKb[$entityName])) {
            $kb = self::$playerKb[$entityName];
            $speed = $kb->getSpeed();
            if ($speed > 0) {
                $event->setAttackCooldown($speed);
            }
        }
    }

    public static function setPlayerKb(string $playerName, KbInfo $kb): void {
        self::$playerKb[$playerName] = $kb;
    }

    public static function getPlayerKb(string $playerName): ?KbInfo {
        return self::$playerKb[$playerName] ?? null;
    }

    public static function removePlayerKb(string $playerName): void {
        unset(self::$playerKb[$playerName]);
    }

    public static function setPlayerKit(string $playerName, string $kitName): void {
        self::$playerKits[$playerName] = $kitName;
    }

    public static function getPlayerKit(string $playerName): ?string {
        return self::$playerKits[$playerName] ?? null;
    }

    public static function removePlayerKit(string $playerName): void {
        unset(self::$playerKits[$playerName]);
    }

    public static function hasKit(string $playerName): bool {
        return isset(self::$playerKits[$playerName]);
    }

    public static function removePlayerLastAttack(string $playerName): void {
        unset(self::$playerLastAttack[$playerName]);
    }
}