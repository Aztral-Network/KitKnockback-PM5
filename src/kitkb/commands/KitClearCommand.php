<?php

declare(strict_types=1);

namespace kitkb\commands;

use kitkb\KitKb;
use kitkb\KitKbListener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class KitClearCommand extends Command
{

    private KitKb $plugin;

    public function __construct(KitKb $plugin)
    {
        parent::__construct('kit-clear', 'Limpiar el kit actual y inventario', 'Usage: /kit-clear', ['clear-kit', 'clearkit']);
        $this->plugin = $plugin;
        $this->setPermission("permission.kit.clear");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if($sender instanceof Player) {

            if(!$this->testPermission($sender))
            {
                return true;
            }

            $sender->getInventory()->clearAll();
            $sender->getArmorInventory()->clearAll();
            $sender->getEffects()->clear();
            KitKbListener::removePlayerKit($sender->getName());
            KitKbListener::removePlayerKb($sender->getName());
            KitKbListener::removePlayerLastAttack($sender->getName());
            $sender->sendMessage(TextFormat::GREEN . "§4HSZ §7: §aKit y inventario limpiados correctamente.");

        } else {
            $sender->sendMessage(KitKb::getConsoleMsg());
        }

        return true;
    }
}
