<?php
namespace SalmonDE\Updater;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Utils;

class UpdaterTask extends PluginTask
{
    public function __construct($url, $hash, $version, $nversion, $owner){
        $this->name = $owner->getDescription()->getName();
        parent::__construct($owner);
        $this->url = $url;
        $this->md5hash = $hash;
        $this->version = $version;
        $this->newversion = $nversion;
    }

    public function onRun($currenttick){
        $file = Utils::getURL($this->url);
        if(md5($file) == $this->md5hash){
            foreach(glob("plugins/".$this->name."*.phar") as $phar){
                unlink($phar);
            }
            file_put_contents('plugins/'.$this->name.'.phar', $file);
            if(!file_exists('plugins/'.$this->name.'.phar')){
                $this->getOwner()->getLogger()->error('Failed to download the update!');
            }else{
                $this->getOwner()->getServer()->broadcastMessage(TF::RED.TF::BOLD.$this->getOwner()->getConfig()->get('Shutdown-Message'));
                $this->getOwner()->getServer()->broadcastTip(TF::RED.TF::BOLD.$this->getOwner()->getConfig()->get('Shutdown-Message'));
                sleep(7);
                $this->getOwner()->getServer()->shutdown();
            }
        }else{
            $this->owner->getLogger()->error('md5 hash of the phar was incorrect');
        }
    }
}
