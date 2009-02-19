<?PHP
	
	class Ship
	{
		function Ship($sid)
		{
			$this->isValid = false;
			
			if (is_array($sid))
			{
				$arr = $sid;
			}
			else
			{
				$res = dbquery("
				SELECT
					*
				FROM	
					ships
				WHERE
					ship_id=".$sid."			
				");			
				if (!$arr = mysql_fetch_assoc($res))
				{
					throw new EException("Ung�ltige Schiff-ID: $sid");
					return false;
				}
			}			
			
			$this->id = $arr['ship_id'];
			$this->name = $arr['ship_name'];
			$this->shortComment = $arr['ship_shortcomment'];
			$this->structure = $arr['ship_structure'];
			$this->shield = $arr['ship_shield'];
			$this->weapon = $arr['ship_weapon'];
			$this->heal = $arr['ship_heal'];
			$this->capacity = $arr['ship_capacity'];
			$this->peopleCapacity = $arr['ship_people_capacity'];
			
			$this->bStructure = $arr['special_ship_bonus_structure'];
			$this->bShield = $arr['special_ship_bonus_shield'];
			$this->bWeapon = $arr['special_ship_bonus_weapon'];

			$this->isValid = true;
		}
		
		
		function isValid() { return $this->isValid; }
		function name() { return $this->name; }
		function shortComment() { return $this->shortComment; }
		function capacity() { return $this->capacity; }
		function peopleCapacity() { return $this->peopleCapacity; }
		
		function __toString()
		{
			return $this->name;
		}
		
		
		function imgPathSmall() 
		{
			return IMAGE_PATH."/".IMAGE_SHIP_DIR."/ship".$this->id."_small.".IMAGE_EXT;			
		}
		
		function imgSmall()
		{
			return "<img src=\"".$this->imgPathSmall()."\" style=\"width:40px;height:40px;\"/>";
		}
		
		static function xpByLevel($base_xp,$factor,$level)
		{
			return $base_xp * intpow($factor,$level-1);
		}
		
		static function levelByXp($base_xp,$factor,$xp)
		{
			return max(0,floor(1 + ((log($xp)-log($base_xp))/log($factor))));
		}
	
	}

?>