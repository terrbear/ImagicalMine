<?php

/*
 *
 *  _                       _           _ __  __ _             
 * (_)                     (_)         | |  \/  (_)            
 *  _ _ __ ___   __ _  __ _ _  ___ __ _| | \  / |_ _ __   ___  
 * | | '_ ` _ \ / _` |/ _` | |/ __/ _` | | |\/| | | '_ \ / _ \ 
 * | | | | | | | (_| | (_| | | (_| (_| | | |  | | | | | |  __/ 
 * |_|_| |_| |_|\__,_|\__, |_|\___\__,_|_|_|  |_|_|_| |_|\___| 
 *                     __/ |                                   
 *                    |___/                                                                     
 * 
 * This program is a third party build by ImagicalMine.
 * 
 * PocketMine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ImagicalMine Team
 * @link http://forums.imagicalcorp.ml/
 * 
 *
*/

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;

class RedstoneWire extends Flowable implements Redstone{
	protected $id = self::REDSTONE_WIRE;
	//protected $power = 0;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getPower(){
		return $this->meta;
	}
	
	public function setPower($power){
		$this->meta = $power;
	}
	
	public function getHardness(){
		return 0;
	}

	public function isSolid(){
		return true;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($down instanceof Transparent && $down->getId() !== Block::GLOWSTONE_BLOCK) return false;
		else{
			$this->getLevel()->setBlock($block, $this, true, true);
			return true;
		}
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$down = $this->getSide(0);
			if($down instanceof Transparent){
				$this->getLevel()->useBreakOn($this);
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return true;
	}
	
	public function fetchMaxPower(){
		$power_in_max = 0;
		for($side = 0; $side <= 5; $side++){
			$near = $this->getSide($side);
			if($near instanceof Redstone){
				$power_in = $near->getPower();
				if($power_in >= 15){
					return 15;
				}
				if($power_in > $power_in_max){
					$power_in_max = $power_in;
				}
			}
		}
		for($side = 2;$side<=5;$side++){
			$near = $this->getSide($side);
			$around_down = $near->getSide(0);
			$around_up = $near->getSide(1);
			if($near->id == self::AIR and $around_down->id==self::REDSTONE_WIRE){
				$power_in = $around_down->getPower();
				if($power_in >= 15){
					return 15;
				}
				if($power_in > $power_in_max){
					$power_in_max = $power_in;
				}
			}
			if(!$near instanceof Transparent and $around_up->id==self::REDSTONE_WIRE){
				$power_in = $around_up->getPower();
				if($power_in >= 15){
					return 15;
				}
				if($power_in > $power_in_max)
					$power_in_max = $power_in;
			}
		}
		return $power_in_max;
	}
	
	public function onRedstoneUpdate($type,$power){
		if($type == Level::REDSTONE_UPDATE_PLACE){
			//$fetchedPower = $this->fetchMaxPower() - 1;
			if($power <= $this->getPower() + 1){
				return;
			}
			$this->setPower($power);
			$this->getLevel()->setBlock($this, $this, true, true);	
		}
		if($type == Level::REDSTONE_UPDATE_BREAK){
		}
	}
	
	public function getName(){
		return "Redstone Wire";
	}

	public function getDrops(Item $item){
		return [[Item::REDSTONE_DUST,0,1]];
	}
	
	public function onBreak(Item $item){
		$oBreturn = $this->getLevel()->setBlock($this, new Air(), true, true);
		return $oBreturn;
	}
	
	public function __toString(){
		return $this->getName() . (isPowered()?"":"NOT ") . "POWERED";
	}
}
