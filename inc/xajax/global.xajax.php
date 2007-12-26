<?PHP

$xajax->register(XAJAX_FUNCTION,'searchUser');
$xajax->register(XAJAX_FUNCTION,'getFlightTargetInfo');
$xajax->register(XAJAX_FUNCTION,'formatNumbers');

//Listet gefundene User auf
function searchUser($val,$field_id='user_nick',$box_id='citybox')
{
	$sOut = "";
	$nCount = 0;
	
	$res=dbquery("SELECT user_nick FROM users WHERE user_nick LIKE '".$val."%' LIMIT 20;");
	if (mysql_num_rows($res)>0)
	{
		while($arr=mysql_fetch_row($res))
		{
			$nCount++;
			$sOut .= "<a href=\"#\" onclick=\"javascript:document.getElementById('".$field_id."').value='".htmlentities($arr[0])."';document.getElementById('".$box_id."').style.display = 'none';\">".htmlentities($arr[0])."</a>";
			$sLastHit = $arr[0];
		}
	}
	
	if($nCount > 20)
	{
		$sOut = "";
	}
	
	$objResponse = new xajaxResponse();
	
	if(strlen($sOut) > 0)
	{
		$sOut = "".$sOut."";
		$objResponse->script("document.getElementById('".$box_id."').style.display = \"block\"");
	}
	else
	{
		$objResponse->script("document.getElementById('".$box_id."').style.display = \"none\"");
	}
	
	//Wenn nur noch ein User in frage kommt, diesen Anzeigen
	if($nCount == 1)
	{
		$objResponse->script("document.getElementById('".$box_id."').style.display = \"none\"");
		$objResponse->script("document.getElementById('".$field_id."').value = \"".$sLastHit."\"");
	}
	
	$objResponse->assign($box_id, "innerHTML", $sOut);
	
	return $objResponse;
}


function getFlightTargetInfo($f,$sx1,$sy1,$cx1,$cy1,$p1)
{
	global $conf,$s;
	$objResponse = new xajaxResponse();
	ob_start();
	$launch = true;
	
	$sx=intval($f['sx']);
	$sy=intval($f['sy']);
	$cx=intval($f['cx']);
	$cy=intval($f['cy']);
	$p=intval($f['p']);
		
	if ($sx<1)
	{
		$sx=1;
		$objResponse->assign("sx","value",1);										
	}
	if ($sy<1)
	{
		$sy=1;
		$objResponse->assign("sy","value",1);										
	}
	if ($cx<1)
	{
		$cx=1;
		$objResponse->assign("cx","value",1);										
	}
	if ($cy<1)
	{
		$cy=1;
		$objResponse->assign("cy","value",1);										
	}
	if ($sx>$conf['num_of_sectors']['p1'])
	{
		$sx=$conf['num_of_sectors']['p1'];
		$objResponse->assign("sx","value",$sx);										
	}
	if ($sy>$conf['num_of_sectors']['p2'])
	{
		$sy=$conf['num_of_sectors']['p2'];
		$objResponse->assign("sy","value",$sy);										
	}
	if ($cx>$conf['num_of_cells']['p1'])
	{
		$cx=$conf['num_of_cells']['p1'];
		$objResponse->assign("cx","value",$cx);										
	}
	if ($cy>$conf['num_of_cells']['p2'])
	{
		$cy=$conf['num_of_cells']['p2'];
		$objResponse->assign("cy","value",$cy);										
	}
	if ($p<1)
	{
		$p=1;
		$objResponse->assign("p","value",$p);										
	}
	if ($p>$conf['num_planets']['p2'])
	{
		$p=$conf['num_planets']['p2'];
		$objResponse->assign("p","value",$p);										
	}

	// Total selected missiles
	$total=0;

	// Calc speed
	if (isset($f['count']))
	{
		foreach ($f['speed'] as $k => $v)
		{
			if (!isset($speed) && $f['count'][$k]>0)
			{
				$speed = $v;
				$total+=$f['count'][$k];
			}
			elseif ($f['count'][$k]>0)
			{
				$speed = min($v,$speed);
				$total+=$f['count'][$k];
			}
		}
		
		// Calc range
		foreach ($f['range'] as $k => $v)
		{
			if (!isset($range) && $f['count'][$k]>0)
			{
				$range = $v;
			}
			elseif ($f['count'][$k]>0)
			{
				$range = min($v,$range);
			}
		}
	}
	
	if ($total>0)
	{
		if ($p>0)
		{
			$res = dbquery("
			SELECT
				planet_name,
				user_nick,
				user_id,
				cell_id,
				planet_id
			FROM
				space_cells
			INNER JOIN
			(
				planets
				LEFT JOIN
					users
					ON planet_user_id=user_id				
			)
			ON 
				planet_solsys_id=cell_id
				AND cell_sx=".intval($sx)."
				AND cell_sy=".intval($sy)."
				AND cell_cx=".intval($cx)."
				AND cell_cy=".intval($cy)."
				AND planet_solsys_pos=".intval($p)."
				
			");
			if (mysql_num_rows($res)>0)
			{
				$arr=mysql_fetch_array($res);
				if ($arr['planet_name']!='')
				{
					$out = "<b>Planet:</b> ".$arr['planet_name'];
				}
				else
				{
					$out = "<i>Unbenannter Planet</i>";
				}
				if ($arr['user_id']>0) 
				{
					$out.=" <b>Besitzer:</b> ".$arr['user_nick'];			
					if ($s['user']['id']==$arr['user_id'] && $arr['user_id']>0)
					{
						$out.=' (Eigener Planet)';								
						$objResponse->assign("targetinfo","style.color",'#f00');								
						$launch=false;
					}
					else
					{
						$objResponse->assign("targetinfo","style.color",'#0f0');								
					}
				}
				else
				{			
					$objResponse->assign("targetinfo","style.color",'#f00');								
					$launch=false;
				}
				$objResponse->assign("targetinfo","innerHTML",$out);								
				$objResponse->assign("targetcell","value",$arr['cell_id']);								
				$objResponse->assign("targetplanet","value",$arr['planet_id']);								
			}			
			else
			{
				$objResponse->assign("targetinfo","innerHTML","Hier existiert kein Planet!");				
				$objResponse->assign("targetinfo","style.color",'#f00');		
				$launch=false;						
				$objResponse->assign("targetcell","value",0);								
				$objResponse->assign("targetplanet","value",0);								
			}

			// Calc time and distance
			$nx=$conf['num_of_cells']['p1'];		// Anzahl Zellen Y
			$ny=$conf['num_of_cells']['p2'];		// Anzahl Zellen X
			$ae=$conf['cell_length']['v'];			// Länge vom Solsys in AE
			$np=$conf['num_planets']['p2'];			// Max. Planeten im Solsys
			$dx = abs(((($sx-1) * $nx) + $cx) - ((($sx1-1) * $nx) + $cx1));
			$dy = abs(((($sy-1) * $nx) + $cy) - ((($sy1-1) * $nx) + $cy1));
			$sd = sqrt(pow($dx,2)+pow($dy,2));			// Distanze zwischen den beiden Zellen
			$sae = $sd * $ae;											// Distance in AE units
			if ($sx1==$sx && $sy1==$sy && $cx1==$cx && $cy1=$cy)
				$ps = abs($p-$p1)*$ae/4/$np;				// Planetendistanz wenn sie im selben Solsys sind
			else
				$ps = ($ae/2) - (($p)*$ae/4/$np);	// Planetendistanz wenn sie nicht im selben Solsys sind
			$ssae = $sae + $ps;
			$timeforflight = $ssae / $speed * 3600;
					
			$objResponse->assign("time","innerHTML",tf($timeforflight));								
			$objResponse->assign("timeforflight","value",$timeforflight);								
			$objResponse->assign("distance","innerHTML",nf($ssae)." AE");								
			if ($ssae > $range)
			{
				$objResponse->assign("distance","style.color","#f00");		
				$objResponse->append("distance","innerHTML"," (zu weit entfernt, ".nf($range)." max)");								
				$launch=false;						
			}
			else
			{
				$objResponse->assign("distance","style.color","#0f0");		
			}
			$objResponse->assign("speed","innerHTML",round($speed,2)." AE/h");			

		}
		else
		{
			$launch=false;
		}
	}
	else
	{
		$objResponse->assign("targetinfo","innerHTML","Keine Raketen gewählt!");								
		$launch=false;		
	}
	
	if ($launch)
	{
		$objResponse->assign("launchbutton","style.color",'#0f0');				
		$objResponse->assign("launchbutton","disabled",false);				
	}
	else
	{
		$objResponse->assign("launchbutton","style.color",'#f00');				
		$objResponse->assign("launchbutton","disabled",true);				
	}
	
	$objResponse->append("targetinfo","innerHTML",ob_get_contents());				
	ob_end_clean();
  return $objResponse;	
}


// Formatiert Zahlen
function formatNumbers($field_id,$val,$format=0,$max)
{
	$objResponse = new xajaxResponse();
	ob_start();
	$val = str_replace('`', '', $val);
	$val = str_replace('k', '000', $val);
	$val = str_replace('m', '000000', $val);
	
	if($max!="")
	{
		$val = min($val,$max);
	}
	
	$val = abs(intval($val));
	
	if(is_integer($val))	
	{
		if($format==1)
		{
			$out = nf($val);
		}
		else
		{
			$out = $val;
		}
	}
	else
	{
		$out = 0;
	}

	$objResponse->assign($field_id,"value",$out);
	
	$objResponse->assign("population_info","innerHTML",ob_get_contents());
	ob_end_clean();
  return $objResponse;	
}



?>