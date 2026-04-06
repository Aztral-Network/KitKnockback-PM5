<?php

declare(strict_types=1);

namespace kitkb\commands;

use kitkb\KitKb;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class DeleteKitCommand extends Command
{

    private KitKb $plugin;

    public function __construct(KitKb $plugin)
    {
        parent::__construct('deleteKit', 'Deletar kit kb', 'Usage: /deleteKit <name>', ['kit-delete', 'deletekit']);
        $this->plugin = $plugin;
        parent::setPermission('permission.kit');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $msg = null;

        if($sender instanceof Player) {
            if($this->testPermission($sender)) {
                if(isset($args[0])) {
                    $name = strval($args[0]);
                    $kitHandler = KitKb::getKitHandler();
                    if($kitHandler->isKit($name)) {
                        $kitHandler->deleteKit($name);
                        $msg = TextFormat::GREEN . 'Kit borrado con sucesso!';
                    } else {
                        $msg = TextFormat::RED . 'Este kit no existe!';
                    }
                } else {
                    $msg = $this->getUsage();
                }
            }
        } else {
            $msg = KitKb::getConsoleMsg();
        }

        if($msg !== null) {
            $sender->sendMessage($msg);
        }

        return true;
    }
}
