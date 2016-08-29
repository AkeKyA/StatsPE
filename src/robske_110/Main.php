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
        $plugin->getServer()->getPluginManager()->registerEvents($plugin, self::$listener);
    }

    /**
     * @param Vector3    $pos          Vector3 of the FloatingText
     * @param string     $levelName    LevelName of the Level the FloatingText should be in
     * @param string     $text         The content of the FloatingText
     * @param string     $title        The title of the FloatingText (If you do not know what this is pass "")
     * @param array|bool $showToPlayer The player the FloatingText should be shown to. If you want to show it to all Player, pass false
    */
    public function __construct(Vector3 $pos, $levelName, $text, $title = "", $showToPlayer = false){
        if(!self::$isInited){
            return false;
        }
        $this->id = self::nextFloatingTextID();
        self::$ftps[$this->id] = [[$levelName, $pos], [$text, $title], $showToPlayer];
        self::renderFT($this->id, self::$ftps[$this->id], true);
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

    private static function renderFT($ftID, $ftData, $fullRender = false){
        self::$floatingTexts[$this->id] = new FloatingText(self::$server->getLevelByName($ftData[0][0]), $ftData[0][1], $ftData[1][0], $ftData[1][1]);
        if($fullRender){
            self::$floatingTexts[$this->id]->update($ftData[2]);
        }
    }

    public static function updateAllFloatingTexts($playerLevelArray = NULL){
        self::hideAllFTPs();
        if($playerLevelArray == NULL){
            $this->showAllFTPs();
        }else{
            foreach($this->floatingTextConfig->getAll() as $ftData){
                $ftData = $ftData[0];
                $this->FloatingTexts[self::$ftIndex] = new FloatingText($this, self::$server->getLevelByName($ftData[0]), new Vector3($ftData[1], $ftData[2], $ftData[3]), $ftData[4]);
                if(isset($this->FloatingTexts)){
                    foreach(self::$server->getOnlinePlayers() as $player){
                        foreach($this->FloatingTexts as $floatingTextObject){
                            if(!isset($playerLevelArray[$player->getName()])){
                                $playerLevel = $player->getLevel()->getName();
                            }else{
                                $playerLevel = $playerLevelArray[$player->getName()];
                            }
                            $FloatingTextLevel = $floatingTextObject->getLevel()->getName();
                            echo("Checking "."PlayerLevel: ".$playerLevel." FTPLevel: ".$FloatingTextLevel." PlayerName: " . $player->getName() . "\n");
                            if($playerLevel == $FloatingTextLevel){
                                $floatingTextObject->update($player);
                                //echo("Re-Created "."PlayerLevel: ".$playerLevel." FTPLevel: ".$FloatingTextLevel." PlayerName: " . $player->getName() . "\n");
                            }
                        }
                    }
                }
                self::$ftIndex++;
            }
        }
    }

    public static function hideAllFTPs(){
        foreach(self::$floatingTexts as $floatingTextObject){
            $floatingTextObject->setInvisible(true);
        }
    }

    public static function showAllFTPs(){
        foreach(self::$ftps as $id => $ftData){
			self::$server->loadLevel($ftData[0][0]);
            self::renderFT($id, $ftData);
            foreach(self::$server->getOnlinePlayers() as $player){
                foreach($this->FloatingTexts as $floatingTextObject){
                    $playerLevel = $player->getLevel()->getName();
                    $FloatingTextLevel = $floatingTextObject->getLevel()->getName();
                    Utils::debug("Checking if should render "."PlayerLevel: ".$playerLevel." FTPLevel: ".$FloatingTextLevel." PlayerName: ".$player->getName());
                    if($playerLevel == $FloatingTextLevel && ($ftData[2] === false || $ftData[2] == $player)){
                        $floatingTextObject->update($player);
                        Utils::debug("Rendered "."PlayerLevel: ".$playerLevel." FTPLevel: ".$FloatingTextLevel." PlayerName: " . $player->getName());
                    }
                }
            }
            self::$ftIndex++;
        }
    }
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!