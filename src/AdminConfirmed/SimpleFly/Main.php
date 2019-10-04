<?php


namespace AdminConfirmed\SimpleFly;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{

    private $config;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->saveResource('Config.yml');
        $this->config = new Config($this->getDataFolder() . 'Config.yml', Config::YAML);
    }

    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();

        if($player->getAllowFlight()) {
            $player->setFlying(false);
            $player->setAllowFlight(false);
            $player->sendMessage(C::RED . "Your flight has been disabled");
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if($command->getName() === "fly"){
            if(!$sender instanceof Player){
                $sender->sendMessage("Please use this comamnd in-game");
                return false;
            }

            if(isset($args[0])){
                if(!$sender->hasPermission("simplefly.command.other")){
                    $sender->sendMessage(C::RED . "You do not have permissions to toggle others flight");
                    return false;
                }
                $target = $sender->getServer()->getPlayer($args[0]);
                if(!$target instanceof Player){
                    $sender->sendMessage(C::RED . "Specified Player could not be found");
                    return false;
                }
                if($target->getAllowFlight()){
                    $target->setFlying(false);
                    $target->setAllowFlight(false);
                    $target->sendMessage(C::RED . "Your flight was toggled off by an Admin");
                    $sender->sendMessage(C::RED . "Toggled " . $target->getName() . "'s flight off");
                } else {
                    $target->setAllowFlight(true);
                    $target->setFlying(true);
                    $target->sendMessage(C::GREEN . "Your flight was toggled on by an Admin");
                    $sender->sendMessage(C::GREEN . "Toggled " . $target->getName() . "'s flight on");
                }
                return false;
            }

            if($sender->getAllowFlight()){
                $sender->setFlying(false);
                $sender->setAllowFlight(false);
                $sender->sendMessage(C::RED . "Toggled your flight off");
            } else {
                $sender->setAllowFlight(true);
                $sender->setFlying(true);
                $sender->sendMessage(C::GREEN . "Toggled your flight on");
            }
        }
        return false;
    }

    public function onEntityDamageEntity(EntityDamageByEntityEvent $event) : void
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();

        if($event->isCancelled() || $this->getConfig()->get("flydisable-onCombat") === false){
            return;
        }
        if(!$entity instanceof Player || !$damager instanceof Player){
            return;
        }

        if($entity->getAllowFlight()){
            $entity->setFlying(false);
            $entity->setAllowFlight(false);
            $entity->sendMessage(C::RED . "Your flight has been disabled");
        }
        if($damager->getAllowFlight()){
            $damager->setFlying(false);
            $damager->setAllowFlight(false);
            $damager->sendMessage(C::RED . "Your flight has been disabled");
        }
    }


}