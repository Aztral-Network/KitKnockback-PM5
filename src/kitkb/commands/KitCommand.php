<?php

declare(strict_types=1);

namespace kitkb\commands;

use kitkb\KitKb;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class KitCommand extends Command
{

    private KitKb $plugin;

    public function __construct(KitKb $plugin)
    {
        parent::__construct('kitkb', 'Pegar kit kb', 'Usage: /kitkb <name>', ['give-kit', 'kit-give']);
        $this->plugin = $plugin;
        $this->setPermission("permission.kit.give");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $msg = null;

        if($sender instanceof Player) {

            if(!$this->testPermission($sender))
            {
                return true;
            }

            if(isset($args[0])) {
                $name = strval($args[0]);
                $kitHandler = KitKb::getKitHandler();
                if($kitHandler->isKit($name)) {
                    $p = $sender;
                    if(\kitkb\KitKbListener::hasKit($p->getName())) {
                        $msg = TextFormat::RED . '§cYou already have a kit! Use /kit-clear to get another one.';
                    } else {
                        $kit = $kitHandler->getKit($name);
                        $kit->giveTo($p);
                    }
                } else {
                    $msg = TextFormat::RED . '§cThis kit doesnt exist!';
                }
            } else {
                $msg = $this->getUsage();
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
