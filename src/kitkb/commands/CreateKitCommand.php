<?php

declare(strict_types=1);

namespace kitkb\commands;

use kitkb\KitKb;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CreateKitCommand extends Command
{

    private KitKb $plugin;

    public function __construct(KitKb $plugin)
    {
        parent::__construct('createKit', 'Criar kit kb', 'Usage: /createKit <name> [xkb] [ykb] [speed]', ['kit-create', 'createkit']);
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
                    if(!$kitHandler->isKit($name)) {
                        $xkb = isset($args[1]) ? floatval($args[1]) : 0.4;
                        $ykb = isset($args[2]) ? floatval($args[2]) : 0.4;
                        $speed = isset($args[3]) ? intval($args[3]) : 10;
                        $kitHandler->createKit($name, $sender, $xkb, $ykb, $speed);
                        $msg = TextFormat::GREEN . 'Kit creado con sucesso!';
                    } else {
                        $msg = TextFormat::RED . 'Este kit ya existe!';
                    }
                } else {
                    $msg = $this->getUsage();
                }
            }
        } else {
            $msg = KitKb::getConsoleMsg();
        }

        if($msg !== null) $sender->sendMessage($msg);

        return true;
    }
}
