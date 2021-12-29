<?php

namespace DaPigGuy\PiggyShopUI\Commands;

use DaPigGuy\PiggyShopUI\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class SellCommand
 * @package DaPigGuy\PiggyShopUI\Commands
 */
class SellCommand extends PluginCommand
{
    /**
     * SellCommand constructor.
     * @param string $name
     * @param Main   $plugin
     */
    public function __construct(string $name, Main $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setDescription("Access the sell UI");
        $this->setPermission("piggyshopui.command.sell");
        $this->setUsage("/sell [category]");
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     * @return bool|mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($sender instanceof Player) {
                if (isset($args[0])) {
                    if (isset($plugin->sellCategories[$args[0]])) {
                        $plugin->openSellCategoryMenu($sender, $args[0]);
                        return;
                    }
                    $sender->sendMessage(TextFormat::RED . "Invalid category.");
                    return;
                }
                $plugin->openSellMainMenu($sender);
                return;
            }
            $sender->sendMessage(TextFormat::RED . "Please use this in-game.");
            return;
        }
    }
}