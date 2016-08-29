<?php
//TODO: Make me not still sticking so hard to the php5 style.
namespace robske_110\EasyFloatingText;

use robske_110\Utils;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;

/**
 * This libary was made for static FTP creation.
*/
class EasyFloatingText
{
    private static $plugin;
    private static $server;
    private static $floatingTexts = [];
    private static $ftps =[];
    private static $ftIndex = 0;
    private $id;

    /**
     * Call init before doing anything else.
    */
    public static function init($plugin){
        self::$listener = new EasyFloatingTextListener();
        self::$plugin = $plugin;
        self::$server = $plugin->getServer();
        $plugin->getServer()->getPluginManager()->registerEvents($plugin, self::Listener);
    }

    /**
     * @param Vector3    $pos       Vector3 of the FloatingText
     * @param string     $levelName LevelName of the Level the FloatingText should be in
     * @param string     $text      The content of the FloatingText
     * @param string     $title     The title of the FloatingText (If you do not know what this is pass "")
     * @param array|bool $showToPlayer The player the FloatingText should be shown to. If you want to show it to all Player, pass false
    */
    public function __construct(Vector3 $pos, $levelName, $text, $title = "", $showToPlayer = false){
        if(!self::$isInited){
            return false;
        }
        $this->id = self::nextFloatingTextID();
        self::$ftps[$this->id] = [[$levelName, $pos], [$text, $title], $showToPlayer];
        #self::renderFT(self::$ftps[$this->id]);
        self::$floatingTexts[$this->id] = new FloatingText(self::$server->getLevelByName($levelName), $pos, $text, $title);
        self::$floatingTexts[$this->id]->update($showToPlayer);
        return true;
    }

    public function getId(){
        return $this->id;
    }

    public static function removeFTP($id){
        self::$floatingTexts[$id]->setInvisible(1);
        unset(self::$floatingTexts[$id]);
        unset(self::$ftps[$id]);
    }

    private static function nextFloatingTextID(){
        return self::$ftIndex++;
    }

    public static function updateAllFloatingTexts($playerLevelArray = NULL){
        self::hideAllFTPs();
        if($playerLevelArray == NULL){
            $this->showAllFTPs();
        }else{
            foreach($this->floatingTextConfig->getAll() as $configFT){
                $configFT = $configFT[0];
                $this->FloatingTexts[$this->IndexFTC] = new FloatingText($this, $this->getServer()->getLevelByName($configFT[0]), new Vector3($configFT[1], $configFT[2], $configFT[3]), $configFT[4]);
                if(isset($this->FloatingTexts)){
                    foreach($this->getServer()->getOnlinePlayers() as $player){
                        foreach($this->FloatingTexts as $FloatingTextObject){
                            if(!isset($playerLevelArray[$player->getName()])){
                                $playerLevel = $player->getLevel()->getName();
                            }else{
                                $playerLevel = $playerLevelArray[$player->getName()];
                            }
                            $FloatingTextLevel = $FloatingTextObject->getLevel()->getName();
                            echo("Checking "."PlayerLevel: ".$playerLevel." FTPLevel: ".$FloatingTextLevel." PlayerName: " . $player->getName() . "\n");
                            if($playerLevel == $FloatingTextLevel){
                                $FloatingTextObject->update($player);
                                //echo("Re-Created "."PlayerLevel: ".$playerLevel." FTPLevel: ".$FloatingTextLevel." PlayerName: " . $player->getName() . "\n");
                            }
                        }
                    }
                }
                $this->IndexFTC++;
            }
        }
    }

    public static function hideAllFTPs(){
        foreach(self::$floatingTexts as $floatingTextObject){
            $floatingTextObject->setInvisible(true);
        }
    }

    public static function showAllFTPs(){
        foreach($this->floatingTextConfig->getAll() as $configFT){
            $configFT = $configFT[0];
			$this->getServer()->loadLevel($configFT[0]);
            $this->FloatingTexts[$this->IndexFTC] = new FloatingText($this, $this->getServer()->getLevelByName($configFT[0]), new Vector3($configFT[1], $configFT[2], $configFT[3]), $configFT[4]);
            if(isset($this->FloatingTexts)){
                foreach($this->getServer()->getOnlinePlayers() as $player){
                    foreach($this->FloatingTexts as $FloatingTextObject){
                        $playerLevel = $player->getLevel()->getName();
                        $FloatingTextLevel = $FloatingTextObject->getLevel()->getName();
                        //echo("Checking "."PlayerLevel: ".$playerLevel." FTPLevel: ".$FloatingTextLevel." PlayerName: " . $player->getName() . "\n");
                        if($playerLevel == $FloatingTextLevel){
                            $FloatingTextObject->update($player);
                            //echo("Re-Created "."PlayerLevel: ".$playerLevel." FTPLevel: ".$FloatingTextLevel." PlayerName: " . $player->getName() . "\n");
                        }
                    }
                }
            }
            $this->IndexFTC++;
            echo("IndexFTC++");
            var_dump($this->IndexFTC);
        }
    }
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!