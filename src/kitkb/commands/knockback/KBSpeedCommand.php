<?php

declare(strict_types=1);

namespace kitkb\commands\knockback;

use kitkb\KitKb;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class KBSpeedCommand extends Command
{

    public function __construct()
    {
        parent::__construct('kbspeed', 'Set kit knockback speed');
        $this->setPermission("permission.kit.kb");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!isset($args[0]) || !isset($args[1])) {
            $sender->sendMessage("Usage: /kbspeed <kit> <value>");
            return false;
        }

        $kitName = strval($args[0]);
        $value = intval($args[1]);

        $kitHandler = KitKb::getKitHandler();
        if (!$kitHandler->isKit($kitName)) {
            $sender->sendMessage(TextFormat::RED . "El kit '$kitName' no existe.");
            return false;
        }

        $kit = $kitHandler->getKit($kitName);
        $kbInfo = $kit->getKbInfo();
        $newKbInfo = new \kitkb\kits\KbInfo($kbInfo->getXKb(), $kbInfo->getYKb(), $value);
        
        $kit->setKbInfo($newKbInfo);
        $kitHandler->updateKit($kit);
        
        $sender->sendMessage(TextFormat::GREEN . "KB-Speed del kit '$kitName' actualizado a: $value");

        return true;
    }
}