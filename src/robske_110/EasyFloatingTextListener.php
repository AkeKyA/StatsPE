<?php

namespace robske_110\EasyFloatingText;

use robske_110\Utils\Utils;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;

class EasyFloatingTextListener extends Listener
{
    public function onPlayerJoin(PlayerJoinEvent $event){
        EasyFloatingText::updateAllFloatingTexts();
    }

    //THIS IS FOR FUTURE VERSIONS, WHERE THE FTPs WILL BE CLICKABLE!
    /*
    public function onDamage(EntityDamageEvent $e){
        $entity = $e->getEntity();
        if($entity instanceof EasyFloatingTextEntity){
            EasyFloatingText::doOnClick($entity->namedtag->AssignedFTPid);
        }
    }
    */

    public function LevelChangeEvent(EntityLevelChangeEvent $event){
        if($event->getEntity() instanceof Player){
            $playerLevel[$event->getEntity()->getName()] = $event->getTarget()->getName();
            EasyFloatingText::updateAllFloatingTexts($playerLevel);
        }
    }
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!