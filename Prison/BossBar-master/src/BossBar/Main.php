<?php

namespace BossBar;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use BossBar\Main;

class Main extends PluginBase implements Listener{

	private static $instance = null;

	public $entityRuntimeId = null, $headBar = '', $cmessages = [], $changeSpeed = 0, $i = 0;

	public $API;

	public function onEnable(){
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->headBar = $this->getConfig()->get('head-message', '');
		$this->cmessages = $this->getConfig()->get('changing-messages', []);
		$this->changeSpeed = $this->getConfig()->get('change-speed', 0);
		if ($this->changeSpeed > 0) $this->getScheduler()->scheduleRepeatingTask(new SendTask($this), 20 * $this->changeSpeed);
	}
	public static function getInstance(){
		return self::$instance;
	}
	public function onLoad(){
		self::$instance = $this;
	}

	public function onJoin(PlayerJoinEvent $ev){
		if (in_array($ev->getPlayer()->getLevel(), $this->getWorlds())){
			if ($this->entityRuntimeId === null){
				$this->entityRuntimeId = API::addBossBar([$ev->getPlayer()], 'Please wait loading BossBar...');
				$this->getServer()->getLogger()->debug($this->entityRuntimeId === NULL ? 'Couldn\'t add BossBar' : 'Successfully added BossBar with EID: ' . $this->entityRuntimeId);
			} else{
				API::sendBossBarToPlayer($ev->getPlayer(), $this->entityRuntimeId, $this->getText($ev->getPlayer()));
				$this->getServer()->getLogger()->debug('Sendt BossBar with existing EID: ' . $this->entityRuntimeId);
			}
		}
	}

	public function sendBossBar(){
		if ($this->entityRuntimeId === null) return;
		$this->i++;
		$worlds = $this->getWorlds();
		foreach ($worlds as $world){
			foreach ($world->getPlayers() as $player){
				API::setTitle($this->getText($player), $this->entityRuntimeId, [$player]);
			}
		}
	}

	public function getText(Player $player){
		$text = '';
		if (!empty($this->headBar)) $text .= $this->formatText($player, $this->headBar) . "\n" . "\n" . TextFormat::RESET;
		$currentMSG = $this->cmessages[$this->i % count($this->cmessages)];
		if (strpos($currentMSG, '%') > -1){
			$percentage = substr($currentMSG, 1, strpos($currentMSG, '%') - 1);
			if (is_numeric($percentage)) API::setPercentage(intval($percentage) + 0.5, $this->entityRuntimeId);
			$currentMSG = substr($currentMSG, strpos($currentMSG, '%') + 2);
		}
		$text .= $this->formatText($player, $currentMSG);
		return mb_convert_encoding($text, 'UTF-8');
	}

	public function formatText(Player $player, string $text){
		$text = str_replace("{display_name}", $player->getDisplayName(), $text);
		$text = str_replace("{prison_rank}", $player->getServer()->getPluginManager()->getPlugin("RankUp")->getRankUpDoesGroups()->getPlayerGroup($player), $text);
		$text = str_replace("{prison_rank_price}", $player->getServer()->getPluginManager()->getPlugin("RankUp")->getRankStore()->getNextRank($player)->getPrice(), $text);
		$text = str_replace("{prison_rank_name}", $player->getServer()->getPluginManager()->getPlugin("RankUp")->getRankStore()->getNextRank($player)->getName(), $text);
		$text = str_replace("{name}", $player->getName(), $text);
		$text = str_replace("{x}", $player->getFloorX(), $text);
		$text = str_replace("{y}", $player->getFloorY(), $text);
		$text = str_replace("{z}", $player->getFloorZ(), $text);
		$text = str_replace("{world}", (($levelname = $player->getLevel()->getName()) === false ? "" : $levelname), $text);
		$text = str_replace("{level_players}", count($player->getLevel()->getPlayers()), $text);
		$text = str_replace("{server_players}", count($player->getServer()->getOnlinePlayers()), $text);
		$text = str_replace("{server_max_players}", $player->getServer()->getMaxPlayers(), $text);
		$text = str_replace("{hour}", date('H'), $text);
		$text = str_replace("{minute}", date('i'), $text);
		$text = str_replace("{second}", date('s'), $text);
		$text = str_replace("{BLACK}", "&0", $text);
		$text = str_replace("{DARK_BLUE}", "&1", $text);
		$text = str_replace("{DARK_GREEN}", "&2", $text);
		$text = str_replace("{DARK_AQUA}", "&3", $text);
		$text = str_replace("{DARK_RED}", "&4", $text);
		$text = str_replace("{DARK_PURPLE}", "&5", $text);
		$text = str_replace("{GOLD}", "&6", $text);
		$text = str_replace("{GRAY}", "&7", $text);
		$text = str_replace("{DARK_GRAY}", "&8", $text);
		$text = str_replace("{BLUE}", "&9", $text);
		$text = str_replace("{GREEN}", "&a", $text);
		$text = str_replace("{AQUA}", "&b", $text);
		$text = str_replace("{RED}", "&c", $text);
		$text = str_replace("{LIGHT_PURPLE}", "&d", $text);
		$text = str_replace("{YELLOW}", "&e", $text);
		$text = str_replace("{WHITE}", "&f", $text);
		$text = str_replace("{OBFUSCATED}", "&k", $text);
		$text = str_replace("{BOLD}", "&l", $text);
		$text = str_replace("{STRIKETHROUGH}", "&m", $text);
		$text = str_replace("{UNDERLINE}", "&n", $text);
		$text = str_replace("{ITALIC}", "&o", $text);
		$text = str_replace("{RESET}", "&r", $text);

		$text = str_replace("&0", TextFormat::BLACK, $text);
		$text = str_replace("&1", TextFormat::DARK_BLUE, $text);
		$text = str_replace("&2", TextFormat::DARK_GREEN, $text);
		$text = str_replace("&3", TextFormat::DARK_AQUA, $text);
		$text = str_replace("&4", TextFormat::DARK_RED, $text);
		$text = str_replace("&5", TextFormat::DARK_PURPLE, $text);
		$text = str_replace("&6", TextFormat::GOLD, $text);
		$text = str_replace("&7", TextFormat::GRAY, $text);
		$text = str_replace("&8", TextFormat::DARK_GRAY, $text);
		$text = str_replace("&9", TextFormat::BLUE, $text);
		$text = str_replace("&a", TextFormat::GREEN, $text);
		$text = str_replace("&b", TextFormat::AQUA, $text);
		$text = str_replace("&c", TextFormat::RED, $text);
		$text = str_replace("&d", TextFormat::LIGHT_PURPLE, $text);
		$text = str_replace("&e", TextFormat::YELLOW, $text);
		$text = str_replace("&f", TextFormat::WHITE, $text);
		$text = str_replace("&k", TextFormat::OBFUSCATED, $text);
		$text = str_replace("&l", TextFormat::BOLD, $text);
		$text = str_replace("&m", TextFormat::STRIKETHROUGH, $text);
		$text = str_replace("&n", TextFormat::UNDERLINE, $text);
		$text = str_replace("&o", TextFormat::ITALIC, $text);
		$text = str_replace("&r", TextFormat::RESET, $text);

		return $text;
	}

	private function getWorlds(){
		$mode = $this->getConfig()->get("mode", 0);
		$worldnames = $this->getConfig()->get("worlds", []);
		$worlds = [];
		switch ($mode){
			case 0:
				$worlds = $this->getServer()->getLevels();
				break;
			case 1:
				foreach ($worldnames as $name){
					if (!is_null($level = $this->getServer()->getLevelByName($name))) $worlds[] = $level;
					else $this->getLogger()->warning("Config error! World " . $name . " not found!");
				}
				break;
			case 2:
				$worlds = $this->getServer()->getLevels();
				foreach ($worlds as $world){
					if (!in_array(strtolower($world->getName()), $worldnames)){
						$worlds[] = $world;
					}
				}
				break;
		}
		return $worlds;
	}
}
