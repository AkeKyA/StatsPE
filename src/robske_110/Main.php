<?php
//TODO: Make me not still sticking so hard to the php5 style.
namespace robske_110\EasyFloatingText;

use robske_110\Utils\Utils;

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
     *
     * @param PluginBase $plugin       The class which extends PluginBase
     */
    public static function init($plugin){
        Utils::debug("[INIT] EsyFltingTxt::init()");
        self::$listener = new EasyFloatingTextListener($plugin);
        self::$plugin = $plugin;
        self::$server = $plugin->getServer();
        Utils::debug("[INIT] Registering events...");
        $plugin->getServer()->getPluginManager()->registerEvents($plugin, self::$listener);
        Utils::debug("[INIT] Done.");
    }

    /**
     * @param Vector3    $pos          Vector3 of the FloatingText
     * @param string     $levelName    LevelName of the Level the FloatingText should be in
     * @param string     $text         The content of the FloatingText
     * @param string     $title        The title of the FloatingText (If you do not know what this is pass "")
     * @param array|bool $showToPlayer The player the FloatingText should be shown to. If you want to show it to all Players, pass false
     *
     * @return bool                    If the FTP has been created.
     */
    public function __construct(Vector3 $pos, $levelName, $text, $title = "", $showToPlayer = false){
        if(!self::$isInited){
            Utils::error("Tried to create a FTP while not inited!");
            return false;
        }
        $this->id = self::nextFloatingTextID();
        self::$ftps[$this->id] = [[$levelName, $pos], [$text, $title], $showToPlayer];
        self::renderFT($this->id, self::$ftps[$this->id], true);
        Utils::debug("Created a FTP (".$this->getId().")");
        return true;
    }

    /**
     * @return int                     The FTPid of the FTP hold by this class.
     */
    public function getId(){
        return $this->id;
    }

    /**
     * REMEMBER TO CLEAR THE REFERENCE TO THE CLASS WHICH YOU USED TO CREATED THE FTP! (#MEMLEAK)
     * 
     * @param int        $id           The FTPid of the FTP you want to remove.
     */
    public static function removeFTP($id){
        self::$floatingTexts[$id]->setInvisible(1);
        unset(self::$floatingTexts[$id]);
        unset(self::$ftps[$id]);
        Utils::debug("Removed a FTP (".$id.")");
    }

    /**
     * Updates and rerenders all FTPs.
     * 
     * @param array  $playerLevelArray An array containing $playerName => $levelObject. This is useful if $player->getLevel() currently doesn't contain the level where the player is actually/will be in.
     */
    public static function updateAllFloatingTexts($playerLevelArray = false){
        Utils::debug("EsyFltingTxt::updateAllFtingTxts()");
        self::hideAllFTPs();
        if(!is_array($playerLevelArray)){
            self::showAllFTPs(false);
        }else{
            self::showAllFTPs();
        }
    }

    private static function nextFloatingTextID(){
        return self::$ftIndex++;
    }

    private static function renderFT($ftID, $ftData, $fullRender = false){ //TODO::checkIfLvlNotLoaded
        self::$floatingTexts[$this->id] = new FloatingText(self::$server->getLevelByName($ftData[0][0]), $ftData[0][1], $ftData[1][0], $ftData[1][1]);
        if($fullRender){
            self::$floatingTexts[$this->id]->update($ftData[2]);
        }
    }

    private static function hideAllFTPs(){
        foreach(self::$floatingTexts as $floatingTextObject){
            $floatingTextObject->setInvisible(true);
        }
    }

    private static function showAllFTPs($playerLevelArray = false){
        foreach(self::$ftps as $id => $ftData){
			if(!self::$server->loadLevel($ftData[0][0])){
                break;
            }
            self::renderFT($id, $ftData);
            foreach(self::$server->getOnlinePlayers() as $player){
                foreach($this->FloatingTexts as $floatingTextObject){
                    if(!$playerLevelArray){
                        if(isset($playerLevelArray[$player->getName()])){
                            $playerLevel = $playerLevelArray[$player->getName()];
                        }else{
                            $playerLevel = $player->getLevel()->getName();
                        }
                    }
                    $playerLevel = $player->getLevel()->getName();
                    $floatingTxtLvl = $floatingTextObject->getLevel()->getName();
                    Utils::debug("Checking if should render "."PlayerLevel: ".$playerLevel." FTPLevel: ".$floatingTxtLvl." PlayerName: ".$player->getName());
                    if($playerLevel == $floatingTxtLvl && ($ftData[2] === false || $ftData[2] == $player)){
                        $floatingTextObject->update($player);
                        Utils::debug("Rendered "."PlayerLevel: ".$playerLevel." FTPLevel: ".$floatingTxtLvl." PlayerName: " . $player->getName());
                    }
                }
            }
            self::$ftIndex++;
        }
    }
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!