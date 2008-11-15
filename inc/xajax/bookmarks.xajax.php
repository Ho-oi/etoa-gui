<?PHP

$xajax->register(XAJAX_FUNCTION,'launchBookmarkProbe');

	function launchBookmarkProbe($bid)
	{
		global $cu;

		$cp = unserialize($_SESSION['currentEntity']);
		
		$objResponse = new xajaxResponse();
		
		ob_start();
		$launched = false;
		$bres = dbquery("
					   	SELECT
							target_id,
							ships,
							res,
							resfetch,
							action
						FROM
							fleet_bookmarks
						WHERE
							id='".$bid."'
							AND user_id='".$cu->id()."';");
		if (mysql_num_rows($bres))
		{
			$barr = mysql_fetch_assoc($bres);
			
			$fleet = new FleetLaunch($cp,$cu);
			if ($fleet->checkHaven())
			{
				$shipOutput;
				$probeCount = true;
				$sidarr = explode(",",$barr['ships']);
				$sres = dbquery("SELECT ship_id,ship_name FROM ships WHERE ship_show=1 ORDER BY ship_type_id,ship_order;");
				while ($sarr = mysql_fetch_row($sres))
				{
					$ships[$sarr[0]] = $sarr[1];
				}
				foreach ($sidarr as $sd)
				{
					$sdi = explode(":",$sd);
					$probeCount = min($probeCount,$fleet->addShip($sdi[0],$sdi[1]));
					$shipOutput .= $sdi[1]." ".$ships[$sdi[0]];
					$shipOutput .= ", ";
				}
				
				if ($probeCount)
				{
					if ($fleet->fixShips())
					{
						if ($ent = Entity::createFactoryById($barr['target_id']))
						{
							if ($fleet->setTarget($ent))
							{
								if ($fleet->checkTarget())
								{
									if ($fleet->setAction($barr['action']))
									{
										if ($fid = $fleet->launch())
										{
											$flObj = new Fleet($fid);
											
											
											$str= "Folgende Schiffe sind unterwegs: $shipOutput. Ankunft in ".tf($flObj->remainingTime());
											$launched = true;
										}
										else
											$str= $fleet->error();
									}
									else
										$str= $fleet->error();
								}
								else
									$str= $fleet->error();
							}
							else
								$str= $fleet->error();
						}
						else
						{
							$str= "Problem beim Finden des Zielobjekts!";
						}
					}
					else
					{
						$str= $fleet->error();
					}				
				}
				else
				{
					$str= "Auf deinem Planeten befinden sich nicht genug Schiffe der ausgewählten Typen!";
				}
			}
			else
			{
				$str= $fleet->error();
			}
		}
		else
		{
			$str= "Du hast noch keine Standard-Spionagesonde gewählt, überprüfe bitte deine <a href=\"?page=userconfig&mode=game\">Spieleinstellungen</a>!";
		}				
		if ($launched)
		{
			echo "<div style=\"color:#0f0\">".$str."<div>";
		}
		else
		{
			echo "<div style=\"color:#f90\">".$str."<div>";
		}
		$objResponse->assign("fleet_info_box","style.display",'block');				
		$objResponse->append("fleet_info","innerHTML",ob_get_contents());				
		ob_end_clean();
	  return $objResponse;	
	}


/*
				$fl = new Fleet($s['user']['id'],"so");
				$fl->setSourceByPlanetId($cid);
				if ($fl->setTargetByPlanetId(intval($tid)))
				{
					if ($fl->target->user_id>0 && $fl->target->user_id!=$s['user']['id'])
					{
						$fl->addShip($s['user']['spyship_id'],$s['user']['spyship_count']);
						$fl->calcDist();
						$fl->calcFlight();
						if ($fl->fuel <= $cif)
						{
							echo "<span style=\"color:#0f0\">Sonde gestartet!</span> 
							Ziel: ".$fl->target->sx."/".$fl->target->sy." : ".$fl->target->cx."/".$fl->target->cy." : ".$fl->target->pp."
							Entfernung: ".nf($fl->distance)." AE, Zeit: ".tf($fl->duration).", Kosten: ".nf($fl->fuel)." ".RES_FUEL."<br/>";
							$fl->launch();
						}
						else
						{
							echo "Zuwenig ".RES_FUEL." für diesen Flug (".$fl->fuel." benötigt)<br/>";
						}							
					}
					else
					{
						echo "Ungültiger Planet!<br/>";
					}
				}
				else
				{
					echo "Ungültiges Ziel!<br/>";
				}*/
?>