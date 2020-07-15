<?php

namespace AntiNuke;

use pocketmine\entity\Effect;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {

	private $breakTimes = [];

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK){
			$this->breakTimes[$event->getPlayer()->getRawUniqueId()] = floor(microtime(true) * 20);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void{
		if(!$event->getInstaBreak()){
			do{
				$oyuncu = $event->getPlayer();
				if(!isset($this->breakTimes[$uuid = $oyuncu->getRawUniqueId()])){
					foreach($this->getServer()->getOnlinePlayers() as $mesaj){
						if($oyuncu->hasPermission("antinuke.yetki")){
							$mesaj->sendMessage("§7{$oyuncu->getName()} §aadlı oyuncu çok hızlı blok kırdı!");
						}
					}
					$this->getLogger()->debug("Oyuncumuz olan" . $oyuncu->getName() . ", hile kullandı!");
					$event->setCancelled();
					break;
				}

				$target = $event->getBlock();
				$esya = $event->getItem();

				$normalZaman = ceil($target->getBreakTime($esya) * 20);

				if($oyuncu->hasEffect(Effect::HASTE)){
					$normalZaman *= 1 - (0.2 * $oyuncu->getEffect(Effect::HASTE)->getEffectLevel());
				}

				if($oyuncu->hasEffect(Effect::MINING_FATIGUE)){
					$normalZaman *= 1 + (0.3 * $oyuncu->getEffect(Effect::MINING_FATIGUE)->getEffectLevel());
				}

				$normalZaman -= 1;

				$HileZaman = ceil(microtime(true) * 20) - $this->breakTimes[$uuid = $oyuncu->getRawUniqueId()];

				if($HileZaman < $normalZaman){
					foreach($this->getServer()->getOnlinePlayers() as $mesaj){
						if($oyuncu->hasPermission("antihack.yetki")){
							$mesaj->sendMessage("§c{$oyuncu->getName()} adlı oyuncu bloğu çok hızlı kırdı! Olması gereken hız; $normalZaman, Yaptığı hız; $HileZaman");
						}
					}
					$this->getLogger()->debug("adlı oyuncu" . $oyuncu->getName() .  "bloğu çok hızlı kırdı! Olması gereken hız; $normalZaman, Yaptığı hız; $HileZaman");
					$event->setCancelled();
					break;
				}

				unset($this->breakTimes[$uuid]);
			}while(false);
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		unset($this->breakTimes[$event->getPlayer()->getRawUniqueId()]);
	}
}
