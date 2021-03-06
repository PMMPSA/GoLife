<?php

/**
 * Copyright 2018-2019 GamakCZ
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace vixikhd\skywars;

use pocketmine\command\Command;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use vixikhd\skywars\arena\Arena;
use vixikhd\skywars\commands\SkyWarsCommand;
use vixikhd\skywars\math\Vector3;
use vixikhd\skywars\provider\YamlDataProvider;

/**
 * Class SkyWars
 * @package skywars
 */
class SkyWars extends PluginBase implements Listener {

    /** @var YamlDataProvider */
    public $dataProvider;

    /** @var Command[] $commands */
    public $commands = [];

    /** @var Arena[] $arenas */
    public $arenas = [];

    /** @var Arena[] $setters */
    public $setters = [];

    /** @var int[] $setupData */
    public $setupData = [];

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->dataProvider = new YamlDataProvider($this);
        $this->getServer()->getCommandMap()->register("SkyWars", $this->commands[] = new SkyWarsCommand($this));
    }

    public function onDisable() {
        $this->dataProvider->saveArenas();
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();

        if(!isset($this->setters[$player->getName()])) {
            return;
        }

        $event->setCancelled(\true);
        $args = explode(" ", $event->getMessage());

        /** @var Arena $arena */
        $arena = $this->setters[$player->getName()];

        switch ($args[0]) {
            case "help":
                $player->sendMessage("??a> Tr??? gi??p thi???t l???p SkyWars (1/1):\n".
                "??7help : Hi???n tr??? b???ng h??? tr??? thi???t l???p\n" .
                "??7slots : C???p nh???t s??? l?????n ng?????i ch??i cho khu v???c\n".
                "??7level : Thi???t l???p level cho khu v???c\n".
                "??7spawn : Thi???t l???p khu v???c h???i sinh\n".
                "??7joinsign : Thi???t l???p b???ng ch???n \n".
                "??7savelevel : L??u l???i c??c ?????a h??nh c???a khu v???c\n".
                "??7enable : K??ch ho???t khu v???c");
                break;
            case "slots":
                if(!isset($args[1])) {
                    $player->sendMessage("??cD??ng: ??7slots <int: slots>");
                    break;
                }
                $arena->data["slots"] = (int)$args[1];
                $player->sendMessage("??a> S??? l?????ng ???????c ?????t l?? $args[1]!");
                break;
            case "level":
                if(!isset($args[1])) {
                    $player->sendMessage("??cD??ng: ??7level <levelName>");
                    break;
                }
                if(!$this->getServer()->isLevelGenerated($args[1])) {
                    $player->sendMessage("??c> ?????a $args[1] kh??ng ???????c t??m th???y!");
                    break;
                }
                $player->sendMessage("??a> ?????a h??nh khu v???c ???????c ?????t l?? $args[1]!");
                $arena->data["level"] = $args[1];
                break;
            case "spawn":
                if(!isset($args[1])) {
                    $player->sendMessage("??cD??ng: ??7setspawn <int: spawn>");
                    break;
                }
                if(!is_numeric($args[1])) {
                    $player->sendMessage("??cS??? th??? t???!");
                    break;
                }
                if((int)$args[1] > $arena->data["slots"]) {
                    $player->sendMessage("??c Hi???n ch??? c?? {$arena->data["slots"]} ch???!");
                    break;
                }

                $arena->data["spawns"]["spawn-{$args[1]}"] = (new Vector3($player->getX(), $player->getY(), $player->getZ()))->__toString();
                $player->sendMessage("??a> Sinh ra $args[1] ?????t t???i X: " . (string)round($player->getX()) . " Y: " . (string)round($player->getY()) . " Z: " . (string)round($player->getZ()));
                break;
            case "joinsign":
                $player->sendMessage("??a> Nh???p v??o b???ng g??? ????? thi???t l???p!");
                $this->setupData[$player->getName()] = 0;
                break;
            case "savelevel":
                if(!$arena->level instanceof Level) {
                    $player->sendMessage("??c> L???i khi l??u th??? gi???i: N?? kh??ng ???????c t??m th???y.");
                    if($arena->setup) {
                        $player->sendMessage("??6> H??y th??? l??u n?? sau khi k??ch ho???t th??? gi???i.");
                    }
                    break;
                }
                $arena->mapReset->saveMap($arena->level);
                $player->sendMessage("??a> ?????a h??nh ???? l??u!");
                break;
            case "enable":
                if(!$arena->setup) {
                    $player->sendMessage("??6> Khu v???c ???? ???????c k??ch ho???t!");
                    break;
                }
                if(!$arena->enable()) {
                    $player->sendMessage("??c> Kh??ng th??? t???i khu v???c, n?? c??n thi???u th??ng tin!");
					$player->sendMessage("??c> H??y ch???c ch???n r???ng b???n ???? thi???t l???p ?????y ?????!");
                    break;
                }
                $player->sendMessage("??a> Khu v???c ???? k??ch ho???t!");
                break;
            case "done":
                $player->sendMessage("??a> B???n ???? r???i kh???i ch??? ????? thi???t ?????t!");
                unset($this->setters[$player->getName()]);
                if(isset($this->setupData[$player->getName()])) {
                    unset($this->setupData[$player->getName()]);
                }
                break;
            default:
                $player->sendMessage("??6> B???n ??ang trong ch??? ????? thi???t ?????t.\n".
                    "??7- D??ng ??lhelp ??r??7????? hi???n th??? l???nh h??? tr???\n"  .
                    "??7- Ho???c ??ldone ??r??????? r???i kh???i ch??? ????? thi???t ?????t");
                break;
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if(isset($this->setupData[$player->getName()])) {
            switch ($this->setupData[$player->getName()]) {
                case 0:
                    $this->setters[$player->getName()]->data["joinsign"] = [(new Vector3($block->getX(), $block->getY(), $block->getZ()))->__toString(), $block->getLevel()->getFolderName()];
                    $player->sendMessage("??a> ???? c???p nh???t!");
                    unset($this->setupData[$player->getName()]);
                    $event->setCancelled(\true);
                    break;
            }
        }
    }
}