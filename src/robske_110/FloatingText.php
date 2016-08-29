<?php

namespace robske_110\EasyFTP;

use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;

class FloatingText extends FloatingTextParticle{
	public static $ftps = [];

	protected $level;
	protected $position;
	public $ftp;

	protected $internalTitle;
	protected $internalText;
	protected $internalInvisible;

	function __construct(Level $level, Vector3 $position, $title){
		$text = NULL;
		parent::__construct($position, $text, $title);
		$this->plugin = $plugin;
		$this->level = $level;
		$this->position = $position;
		$this->internalTitle = $title;
		$this->internalText = $text;
		if($title instanceof \Closure) $title = call_user_func($title);
		if($text instanceof \Closure) $text = call_user_func($text);
		$this->ftp = new FloatingTextParticle($position, $text, $title);
		array_push(self::$ftps, $this);
	}

	public function setText($text, Player $player = null){
		$this->internalText = $text;
		$this->update($player);
	}
	public function setTitle($title, Player $player = null){
		$this->internalTitle = $title;
		$this->update($player);
	}
	public function setInvisible($value = true, Player $player = null){
		$this->internalInvisible = $value;
		$this->update($player);
	}

	public function isInvisible(){
		return $this->internalInvisible;
	}
	public function getTitle(){
		if($this->internalTitle instanceof \Closure) return call_user_func($this->internalTitle);
		return $this->internalTitle;
	}
	public function getText(){
		if($this->internalText instanceof \Closure) return call_user_func($this->internalText);
		return $this->internalText;
	}

	public function update(Player $player = null){
		$this->ftp->setText($this->getText());
		$this->ftp->setTitle($this->getTitle());
		$this->ftp->setInvisible($this->internalInvisible);
		if($player === null || $player === false){
			$this->showToAll();
		}else{
			$this->internalInvisible = false;
			$this->showToPlayer($player);
		}
	}

	public function showToAll(){
		$this->level->addParticle($this->ftp, $this->level->getPlayers());
	}
	public function showToPlayer(Player $player){
		$this->level->addParticle($this->ftp, [$player]);
	}

	public function getLevel(){
		return $this->level;
	}
}