<?php

// MineDogsTeam @EmreTr1

namespace turfwars\tw;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\sound\ClickSound;
use pocketmine\level\Position;
use pocketmine\event\entity\EntityTeleportEvent;

use Main\BeginTask;

class Main extends PluginBase implements Listener{

    public function OnEnable(){
        $this->getLogger()->info(TextFormat::GREEN . "TurfWars Plugini Açıldı!!");
	}

    public function OnDisable(){
        $this->getLogger()->info(TextFormat::RED . "TurfWars Plugini Kapandıı!!");
	}
	
    public function OnJoin(PlayerJoinEvent $player){
        if($player->isOnline()){
		$player->sendMessage("§aWelocome to The §bTurfWars §aServer! §eYou Playing On §5***.***.** Server! §6Have Fun :)");
		$player->getInventory()->addItem(Item::get(Item::CLOCK, 0, 1));
		}
	}
	
    public function onRun($tick){
	    foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
		    if($this->plugin->gameduration == 642){	
		        $onp = count($this->plugin->getServer()->getOnlinePlayers());	
		        $need = 8;
		        $total = $need - $onp;
		        $t2 = $onp - $need;
                $p->sendTip("Online: ".count($this->plugin->getServer()->getOnlinePlayers())."/8");
		        $p->sendPopup("".$total. "Need Player!");
			}
		}
	}
}
