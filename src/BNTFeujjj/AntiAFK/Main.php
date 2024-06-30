<?php

namespace BNTFeujjj\AntiAFK;

use BNTFeujjj\AntiAFK\tasks\AFKTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    public array $afkTime = [];
    public string $maxAFKTime;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->maxAFKTime = $this->getConfig()->get("afk.time");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new AFKTask($this), 20);
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $now = new \DateTime("now", new \DateTimeZone($this->getConfig()->get('time.zone')));
        $now->add(new \DateInterval($this->maxAFKTime));
        $this->afkTime[$player->getName()] = $now;
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        unset($this->afkTime[$player->getName()]);
    }

    public function onMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $now = new \DateTime("now", new \DateTimeZone($this->getConfig()->get('time.zone')));
        $now->add(new \DateInterval($this->maxAFKTime));
        $this->afkTime[$player->getName()] = $now;
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
        if($cmd->getName() === "afkreload") {
            if(!$sender->hasPermission("antiafk.cmd.reload")) {
                return false;
            }
            $this->reloadConfig();
            $sender->sendMessage($this->getConfig()->get("reload.config.message"));
            return true;
        }
        return false;
    }
}
