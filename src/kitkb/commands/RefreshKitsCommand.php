<?php

declare(strict_types=1);

namespace kitkb\commands;

use kitkb\KitKb;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class RefreshKitsCommand extends Command
{

    private KitKb $plugin;

    public function __construct(KitKb $plugin)
    {
        parent::__construct('kits-refresh', 'Refresh/Reload kit configuration');
        $this->plugin = $plugin;
        $this->setPermission("permission.kit");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $kitHandler = KitKb::getKitHandler();
        $kitHandler->reloadConfig();
        
        $sender->sendMessage(TextFormat::GREEN . "Kit configuration refreshed successfully!");
        return true;
    }
}