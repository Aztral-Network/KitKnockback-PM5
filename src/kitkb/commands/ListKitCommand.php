<?php

declare(strict_types=1);

namespace kitkb\commands;

use kitkb\KitKb;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ListKitCommand extends Command
{

    private KitKb $plugin;

    public function __construct(KitKb $plugin)
    {
        parent::__construct("listKits", "Lists all of the kits.", "Usage: /listKits", ["kitlist", "listkits"]);
        $this->plugin = $plugin;
        $this->setPermission("permission.kit.list");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if($this->testPermission($sender))
        {
            $kits = KitKb::getKitHandler()->getKits();
            $message = TextFormat::GOLD . "§l§bKITS:§r§7 " . TextFormat::WHITE;
            if(count($kits) <= 0)
            {
                $sender->sendMessage($message . "nenhum");
                return true;
            }

            $kitExtension = [];
            foreach($kits as $kit)
            {
                $kitExtension[] = $kit->getName();
            }
            $sender->sendMessage($message . implode(", ", $kitExtension));
        }

        return true;
    }
}
