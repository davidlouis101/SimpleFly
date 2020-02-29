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
            $player->sendMessage(C::RED . "Fly Deaktiviert");
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if($command->getName() === "fliegen"){
            if(!$sender instanceof Player){
                $sender->sendMessage("Please use this comamnd in-game");
                return false;
            }

            if(isset($args[0])){
                if(!$sender->hasPermission("simplefly.command.other")){
                    $sender->sendMessage(C::RED . "Du Kannst Fly Für Andere Nicht Deaktivieren");
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
                    $target->sendMessage(C::RED . "Dein Fly-Mode Wurde von Ein Administrator Deaktiviert");
                    $sender->sendMessage(C::RED . "Fly Von" . $target->getName() . "'Wurde Deaktiviert");
                } else {
                    $target->setAllowFlight(true);
                    $target->setFlying(true);
                    $target->sendMessage(C::GREEN . "Dein Fly-Mode Wurde von Ein Administrator Aktiviert");
                    $sender->sendMessage(C::GREEN . "Fly Von" . $target->getName() . "Wurde Aktiviert");
                }
                return false;
            }

            if($sender->getAllowFlight()){
                $sender->setFlying(false);
                $sender->setAllowFlight(false);
                $sender->sendMessage(C::RED . "Dein Fliegen Wurde Deaktiviert");
            } else {
                $sender->setAllowFlight(true);
                $sender->setFlying(true);
                $sender->sendMessage(C::GREEN . "Dein Fliegen Wurde Aktiviert");
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
            $entity->sendMessage(C::RED . "Dein Fliegen Wurde Deaktiviert");
        }
        if($damager->getAllowFlight()){
            $damager->setFlying(false);
            $damager->setAllowFlight(false);
            $damager->sendMessage(C::RED . "Dein Fliegen Wurde Deaktiviert");
        }
    }


}
