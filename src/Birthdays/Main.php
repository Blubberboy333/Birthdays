<?php

namespace Birthdays;

use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Main extends PluginBase implements Listener{
	public $date;
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		if(!(is_dir($this->getDataFolder()."Birtydays"))){
			@mkdir($this->getDataFolder()."Birthdays");
			$this->getLogger()->info(TextFormat::YELLOW."Made a path file for Birthdays");
		}
		$this->getLogger()->info(TextFormat::GREEN."Birthdays enabled");
		$this->getLogger()->info(TextFormat::GREEN."Today's date is ".date('l').", ".date('F')." ".date('d').", ".date('o'));
		$this->getLogger()->info(TextFormat::GREEN."And the time is ".date('g').":".date('i'));
	}
	
	public function onDisable(){
		$this->getLogger()->info(TextFormat::RED."Birthdays disabled");
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch(strtolower($command->getName())){
			case "birthday":
			if($sender->hasPermission("birthday") || $sender->hasPermission("birthday.command") || $sender->hasPermission("birthday.command.set")){
				if(!(isset($args[0]))){
					return false;
				}else{
					if(!(isset($args[1]))){
						$sender->sendMessage(TextFormat::YELLOW."You didn't set a month or a day");
						return false;
					}else{
						if(!(isset($args[2]))){
							$sender->sendMessage(TextFormat::YELLOW."You didn't set a day");
							return false;
						}else{
							if(!(isset($args[3]))){
								if(file_exists($this->getDataFolder()."Birthdays/".$sender->getName().".yml")){
									if($this->getBirthday($sender->getName())->get("AmountofSets") <= $this->getConfig()->get("AmountofSets")){
										$sender->sendMessage("You can't change your birthday anymore");
									}else{
										$this->getBirthday($sender->getName())->set("Date", $args[1]."/".$args[2]);
										$this->getBirthday($sender->getName())->set("AmountofSets", +1);
										$this->getBirthday($sender->getName())->save();
										$sender->sendMessage("Your birthday has been changed to ".$args[1]."/".$args[2]);
										return true;
									}
								}else{
									$this->addBirthday($sender->getName(), $args[1], $args[2]);
									return true;
								}
							}else{
								if($sender->hasPermission("birthday") || $sender->hasPermission("birthday.command") || $sender->hasPermission("birthday.command.other")){
									$player = $this->getServer()->getPlayer($args[3]);
									if($player instanceof Player){
										if(file_exists($this->getDataFolder()."Birthdays/".$player->getName().".yml")){
											$file = $this->getBirthday($player->getName());
											$file->set("AmountofSets", +1);
											$file->set("Date", $args[1]."/",$args[2]);
											$file->save();
											$sender->sendMessage("Birthday changed to ".$file->get("Date")." for ".$player->getName());
											$player->sendMessage("Your birthday has been changed to ".$file->get("Date"));
											return true;
										}else{
											$this->addBirthday($player->getName(), $args[1], $args[2]);
											return true;
										}
									}else{
										$sender->sendMessage("That player could not be foun");
										return true;
									}
								}else{
									$sender->sendMessage("You cannot change another player's birthday");
									return true;
								}
							}
						}
					}
				}
			}else{
				$sender->sendMessage("You don't have permission to do that!");
				return true;
			}
			// case here
		}
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(!(file_exists($this->getDataFolder()."Birthdays/".$name.".yml"))){
			$player->sendMessage("You haven't set a birthay yet! Use /birthday set to set one!");
		}else{
			if($this->getBirthday($name)->get("Date") == date(M)."/".date(d)){
				if(!($this->getConfig()->get("Message") !== "nothing")){
					$cnfgMessage = str_replace("{PLAYER}", $name);
					$this->getServer()->broadcastMessage($cnfgMessage);
				}
				if(!($this->getConfig()->get("Popup"))){
					$player->sendPopup($this->getConfig()->get("Popup"));
				}
				foreach($this->getConfig()->get("Items") as $i){
					$player->getInventory()->addItem($i);
				}
			}
		}
	}
	
	public function getBirthday($player){
		if(file_exists($this->getDataFolder()."Birthdays/".$player.".yml")){
			$inFile = new Config($this->getDataFolder()."Birthdays/".$player.".yml", Config::YAML);
			return $inFile->getAll();
		}
	}
	
	public function addBirthday($file, $m, $d){
		if(file_exists($this->getDataFolder()."Birthdays/".$file.".yml")){
			return "There is already a file set for ".$file;
		}else{
			$file = new Config($this->getDataFolder()."Birthdays/".$file.".yml", Config::YAML);
			$file->set("AmountofSets", 1);
			$file->set("Date", $m."/".$d);
			$file->save();
		}
	}
	
	public function delBirthday($file){
		if(file_exists($this->getDataFolder()."Birthdays/".$file.".yml")){
			unlink($this->getDataFolder()."Birthdays/".$file.".yml");
			return "Birthday deleted!";
		}else{
			return "There is no birthday by the name of ".$file;
		}
	}
}
