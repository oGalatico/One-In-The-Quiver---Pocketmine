<?php
namespace TheQuiver;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\OfflinePlayer;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\CallbackTask;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\sound\FizzSound;
use pocketmine\level\sound\ClickSound;

class Main extends PluginBase implements Listener
{

	private static $obj = null;
	public static function getInstance()
	{
		return self::$obj;
	}
	public function onEnable()
	{
		if(!self::$obj instanceof Main)
		{
			self::$obj = $this;
		}
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"gameTimber"]),20);
		@mkdir($this->getDataFolder(), 0777, true);
		$this->config=new Config($this->getDataFolder() . "config.yml", Config::YAML, array());
		if($this->config->exists("lastpos"))
		{
			$this->lobby=$this->config->get("lobby");
			$this->pos1=$this->config->get("pos1");
			$this->pos2=$this->config->get("pos2");
			$this->pos3=$this->config->get("pos3");
			$this->pos4=$this->config->get("pos4");
			$this->pos5=$this->config->get("pos5");
			$this->pos6=$this->config->get("pos6");
			$this->pos7=$this->config->get("pos7");
			$this->pos8=$this->config->get("pos8");
			$this->lastpos=$this->config->get("lastpos");
			$this->level=$this->getServer()->getLevelByName($this->config->get("pos1")["level"]);
			$this->lobby=new Vector3($this->lobby["x"],$this->lobby["y"],$this->lobby["z"]);
			$this->pos1=new Vector3($this->pos1["x"]+0.5,$this->pos1["y"],$this->pos1["z"]+0.5);
			$this->pos2=new Vector3($this->pos2["x"]+0.5,$this->pos2["y"],$this->pos2["z"]+0.5);
			$this->pos3=new Vector3($this->pos3["x"]+0.5,$this->pos3["y"],$this->pos3["z"]+0.5);
			$this->pos4=new Vector3($this->pos4["x"]+0.5,$this->pos4["y"],$this->pos4["z"]+0.5);
			$this->pos5=new Vector3($this->pos5["x"]+0.5,$this->pos5["y"],$this->pos5["z"]+0.5);
			$this->pos6=new Vector3($this->pos6["x"]+0.5,$this->pos6["y"],$this->pos6["z"]+0.5);
			$this->pos7=new Vector3($this->pos7["x"]+0.5,$this->pos7["y"],$this->pos7["z"]+0.5);
			$this->pos8=new Vector3($this->pos8["x"]+0.5,$this->pos8["y"],$this->pos8["z"]+0.5);
			$this->lastpos=new Vector3($this->lastpos["x"]+0.5,$this->lastpos["y"],$this->lastpos["z"]+0.5);
		}
		if(!$this->config->exists("endTime"))
		{
			$this->config->set("endTime",180);
		}
		if(!$this->config->exists("gameTime"))
		{
			$this->config->set("gameTime",300);
		}
		if(!$this->config->exists("waitTime"))
		{
			$this->config->set("waitTime",180);
		}
		if(!$this->config->exists("godTime"))
		{
			$this->config->set("godTime",15);
		}
		$this->endTime=(int)$this->config->get("endTime");//游戏时间
		$this->gameTime=(int)$this->config->get("gameTime");//游戏时间
		$this->waitTime=(int)$this->config->get("waitTime");//等待时间
		$this->godTime=(int)$this->config->get("godTime");//无敌时间
		$this->gameStatus=0;//Mevcut durum
		$this->lastTime=0;//还没开始
		$this->players=array();//加入游戏的玩家
		$this->SetStatus=array();//设置状态
		$this->all=0;//最大玩家数量
		$this->config->save();
		$this->getServer()->getLogger()->info(TextFormat::GRAY."<-------------------------------------------------------------------------------->");
		$this->getServer()->getLogger()->info(TextFormat::GREEN."[OneTheQuiver] Plugin Aktivite Edildi");
		$this->getServer()->getLogger()->info(TextFormat::AQUA."[OneTheQuiver] Plugin Yapimi : EmreTr1, Eklenti Basariyla Calisiyor İyi Eglenceler :)");
		$this->getServer()->getLogger()->info(TextFormat::RED."[OneTheQuiver] Minedogs SW Hata Veya , Crash Yok ! İyi ! Calisiyor...:)");
	    $this->getServer()->getLogger()->info(TextFormat::GOLD."[OneTheQuiver] Şimdi OneTheQuiver Zamanı :D");
		$this->getServer()->getLogger()->info(TextFormat::GRAY."<-------------------------------------------------------------------------------->");
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		if($command->getName()=="lobby")
		{
			if($this->gameStatus>=2)
			{
				$sender->sendMessage("[OneTheQuiver] Bu aşamada çıkamazsın !");
				return;
			}
			if(isset($this->players[$sender->getName()]))
			{	
				unset($this->players[$sender->getName()]);
				$sender->setLevel($this->signlevel);
				$sender->teleport($this->signlevel->getSpawnLocation());
				$sender->sendMessage("§a[OneTheQuiver] §l§6Oyundan §bÇıkılıyor..");
				$p->setHealth(20);
				$this->sendToAll("§e[OneTheQuiver]§7".$sender->getName()." Oyundan Ayrıldı .");
				$this->changeStatusSign();
				if($this->gameStatus==1 && count($this->players)<2)
				{
					$this->gameStatus=0;
					$this->lastTime=0;
					$this->sendToAll("§e[OneTheQuiver] §f Oyunun Başlaması İçin Yeterli Oyuncu Yok!");
					foreach($this->players as $pl)
					{
						$p=$this->getServer()->getPlayer($pl["id"]);
						$p->setLevel($this->signlevel);
						$p->teleport($this->signlevel->getSpawnLocation());
						unset($p,$pl);
					}
				}
			}
			else
			{
				$sender->sendMessage("§9> [§aOyun Başlatılıyor !!");
			}
			return true;
		}
		if(!isset($args[0])){unset($sender,$cmd,$label,$args);return false;};
		switch ($args[0])
		{
		case "set":
			if($this->config->exists("lastpos"))
			{
				$sender->sendMessage("[OneTheQuiver] Oyun Hazir Lutfen İlk Seti Sil!");
			}
			else
			{
				$name=$sender->getName();
				$this->SetStatus[$name]=0;
				$sender->sendMessage("[OneTheQuiver] §9 (KURULUM) §bBir Tabelaya Tıkla !");
			}
			break;
		case "remove":
			$this->config->remove("sign");
			$this->config->remove("pos1");
			$this->config->remove("pos2");
			$this->config->remove("pos3");
			$this->config->remove("pos4");
			$this->config->remove("pos5");
			$this->config->remove("pos6");
			$this->config->remove("pos7");
			$this->config->remove("pos8");
			$this->config->remove("lastpos");
			$this->config->save();
			unset($this->sign,$this->pos1,$this->pos2,$this->pos3,$this->pos4,$this->pos5,$this->pos6,$this->pos7,$this->pos8,$this->lastpos);
			$sender->sendMessage("[§eOneTheQuiver] §9(KURULUM) §aHedefi Başarıyla Seçildi!");
			break;
		case "start":
			$this->sendToAll("§e[OneTheQuiver]§a Oyun Başladı.");
			$this->gameStatus=1;
			$this->lastTime=5;
			break;
		case "reload":
			unset($this->config);
			@mkdir($this->getDataFolder(), 0777, true);
			$this->config=new Config($this->getDataFolder() . "config.yml", Config::YAML, array());
			if($this->config->exists("lastpos"))
			{
				$this->lobby=$this->config->get("lobby");
				$this->pos1=$this->config->get("pos1");
				$this->pos2=$this->config->get("pos2");
				$this->pos3=$this->config->get("pos3");
				$this->pos4=$this->config->get("pos4");
				$this->pos5=$this->config->get("pos5");
				$this->pos6=$this->config->get("pos6");
				$this->pos7=$this->config->get("pos7");
				$this->pos8=$this->config->get("pos8");
				$this->lastpos=$this->config->get("lastpos");
				$this->level=$this->getServer()->getLevelByName($this->config->get("pos1")["level"]);
				$this->lobby=new Vector3($this->lobby["x"],$this->lobby["y"],$this->lobby["z"]);
				$this->pos1=new Vector3($this->pos1["x"]+0.5,$this->pos1["y"],$this->pos1["z"]+0.5);
				$this->pos2=new Vector3($this->pos2["x"]+0.5,$this->pos2["y"],$this->pos2["z"]+0.5);
				$this->pos3=new Vector3($this->pos3["x"]+0.5,$this->pos3["y"],$this->pos3["z"]+0.5);
				$this->pos4=new Vector3($this->pos4["x"]+0.5,$this->pos4["y"],$this->pos4["z"]+0.5);
				$this->pos5=new Vector3($this->pos5["x"]+0.5,$this->pos5["y"],$this->pos5["z"]+0.5);
				$this->pos6=new Vector3($this->pos6["x"]+0.5,$this->pos6["y"],$this->pos6["z"]+0.5);
				$this->pos7=new Vector3($this->pos7["x"]+0.5,$this->pos7["y"],$this->pos7["z"]+0.5);
				$this->pos8=new Vector3($this->pos8["x"]+0.5,$this->pos8["y"],$this->pos8["z"]+0.5);
				$this->lastpos=new Vector3($this->lastpos["x"]+0.5,$this->lastpos["y"],$this->lastpos["z"]+0.5);
			}
			if(!$this->config->exists("endTime"))
			{
				$this->config->set("endTime",600);
			}
			if(!$this->config->exists("gameTime"))
			{
				$this->config->set("gameTime",300);
			}
			if(!$this->config->exists("waitTime"))
			{
				$this->config->set("waitTime",180);
			}
			if(!$this->config->exists("godTime"))
			{
				$this->config->set("godTime",15);
			}
			$this->endTime=(int)$this->config->get("endTime");//游戏时间
			$this->gameTime=(int)$this->config->get("gameTime");//游戏时间
			$this->waitTime=(int)$this->config->get("waitTime");//等待时间
			$this->godTime=(int)$this->config->get("godTime");//无敌时间
			$this->gameStatus=0;//当前状态
			$this->lastTime=0;//还没开始
			$this->players=array();//加入游戏的玩家
			$this->SetStatus=array();//设置状态
			$this->all=0;//最大玩家数量
			$this->config->save();
			$sender->sendPopUp("[游戏系统] [饥饿游戏] 重载完成");
			break;
		default:
			return false;
			break;
		}
		return true;
	}
	
	public function onPlace(BlockPlaceEvent $event)
	{
		if(!isset($this->sign))
		{
			return;
		}
		$block=$event->getBlock();
		if($this->PlayerIsInGame($event->getPlayer()->getName()) || $block->getLevel()==$this->level)
		{
			if(!$event->getPlayer()->isOp())
			{
				$event->setCancelled();
			}
		}
		unset($block,$event);
	}
	
	public function onMove(PlayerMoveEvent $event)
	{
		if(!isset($this->sign))
		{
			return;
		}
		if($this->PlayerIsInGame($event->getPlayer()->getName()) && $this->gameStatus<=1)
		{
			if(!$event->getPlayer()->isOp())
			{
				$event->setCancelled();
			}
		}
		unset($event);
	}
	public function onBreak(BlockBreakEvent $event)
	{
		if(!isset($this->sign))
		{
			return;
		}
		$sign=$this->config->get("sign");
		$block=$event->getBlock();
		if($this->PlayerIsInGame($event->getPlayer()->getName()) || ($block->getX()==$sign["x"] && $block->getY()==$sign["y"] && $block->getZ()==$sign["z"] && $block->getLevel()->getFolderName()==$sign["level"]) || $block->getLevel()==$this->level)
		{
			if(!$event->getPlayer()->isOp())
			{
				$event->setCancelled();
			}
		}
		unset($sign,$block,$event);
	}
	
	public function onPlayerCommand(PlayerCommandPreprocessEvent $event)
	{
		if(!$this->PlayerIsInGame($event->getPlayer()->getName()) || $event->getPlayer()->isOp() || substr($event->getMessage(),0,1)!="/")
		{
			unset($event);
			return;
		}
		switch(strtolower(explode(" ",$event->getMessage())[0]))
		{
		case "/kill":
		case "/lobby":
			
			break;
		default:
			$event->setCancelled();
			$event->getPlayer()->sendMessage("TEST yOUr Player");
			$event->getPlayer()->sendMessage(" §e[INFO] §7Oyundan Çıkmak İçin §5/kill§e veya§b /lobby §eKomutunu Girebilirsiniz ");
			break;
		}
		unset($event);
	}
	
	public function onDamage(EntityDamageEvent $event)
	{
		$player = $event->getEntity();
		if ($event instanceof EntityDamageByEntityEvent)
		{
        	$player = $event->getEntity();
        	$killer = $event->getDamager();
			if($player instanceof Player && $killer instanceof Player)
			{
		    	if($this->PlayerIsInGame($player->getName()) && ($this->gameStatus==2 || $this->gameStatus==1))
		    	{
		    		$event->setCancelled();
		    	}
		    	if($this->PlayerIsInGame($player->getName()) && !$this->PlayerIsInGame($killer->getName()) && !$killer->isOp())
		    	{
		    		$event->setCancelled();
		    		$killer->sendPopUp("Played");
		    		$killer->kill();
		    	}
		    }
		}
		
		unset($player,$killer,$event);
	}
	
	public function PlayerIsInGame($name)
	{
		return isset($this->players[$name]);
	}
	
	public function PlayerDeath(PlayerDeathEvent $event){
		$event->getPlayer()->setLevel($this->level);
		$event->getPlayer()->teleport($this->pos".$i.");
		$event->getPlayer()->sendMessage("§aRespawning...");
	}
	
	public function sendToAll($msg){
		foreach($this->players as $pl)
		{
			$this->getServer()->getPlayer($pl["id"])->sendMessage($msg);
		}
		$this->getServer()->getLogger()->info($msg);
		unset($pl,$msg);
	}
	
	public function gameTimber(){
		if(!isset($this->lastpos) || $this->lastpos==array())
		{
			return;
		}
		if(!$this->signlevel instanceof Level)
		{
			$this->level=$this->getServer()->getLevelByName($this->config->get("pos1")["level"]);
			$this->signlevel=$this->getServer()->getLevelByName($this->config->get("sign")["level"]);
			if(!$this->signlevel instanceof Level)
			{
				return;
			}
		}
		$this->changeStatusSign();
		if($this->gameStatus==0)
		{
			$i=0;
			foreach($this->players as $key=>$val)
			{
				$i++;
				$p=$this->getServer()->getPlayer($val["id"]);
				echo($i."\n");
				$p->setLevel($this->level);
				eval("\$p->teleport(\$this->lobby);");
				unset($p);
			}
		}
		if($this->gameStatus==1)
		{
			$this->lastTime--;
			$i=0;
			foreach($this->players as $key=>$val)
			{
				$i++;
				$p=$this->getServer()->getPlayer($val["id"]);
				echo($i."\n");
				$p->setLevel($this->level);
				eval("\$p->teleport(\$this->pos".$i.");");
				unset($p);
			}
			switch($this->lastTime)
			{
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 5:
			case 6:
			case 7:
			case 8:
			case 9:
			case 10:
			case 20:
			case 30:
				$this->sendToAll("§a§aOneTheQuiver] Başlıyorr §d" .$this->lastTime. " §bSaniye");
				break;
			case 60:
				$this->sendToAll(" §aOneTheQuiver] §eOyunun Başlamasına §b1 Dakika §aKaldı ! ");
				break;
			case 90:
				$this->sendToAll(" §a[OneTheQuiver] §eOyunun Başlamasına §b1 Dakika §a 30 Saniye §dKaldı !");
				break;
			case 120:
				$this->sendToAll(" §a[OneTheQuiver] §eOyunun Başlamasına §b2 Dakika §dKaldı ! ");
				break;
			case 150:
				$this->sendToAll(" §a[OneTheQuiver] §eOyunun Başlamasına §b2 Dakika §a30 Saniye §9Kaldı ! ");
				break;
			case 0:
				$this->gameStatus=2;
				$this->sendToAll("§aGame Started!!!");
				$p->getLevel()->addSound(new FizzSound($p));
				$this->lastTime=$this->godTime;
				$this->resetChest();
				foreach($this->players as $key=>$val)
				{
					$p=$this->getServer()->getPlayer($val["id"]);
					$p->setMaxHealth(20);
					$p->setHealth(20);
					$p->setLevel($this->level);
				}
				$this->all=count($this->players);
				break;
			}
		}
		//I'm working on about...
