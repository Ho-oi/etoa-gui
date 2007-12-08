<?PHP

	$xajax->register(XAJAX_FUNCTION,'registerCheckName');
	$xajax->register(XAJAX_FUNCTION,'registerCheckNick');
	$xajax->register(XAJAX_FUNCTION,'registerCheckEmail');
	$xajax->register(XAJAX_FUNCTION,'registerShowRace');

//Überprüft die Korrektheit der Eingabe von Vor- und Nachname
function registerCheckName($val)
{
	global $db_table, $db;

	$objResponse = new xajaxResponse();
	if (checkValidName($val))
	{
		if (ereg("^.+ .+( .*)*$", $val))
		{
  		$objResponse->assign('nameStatus', 'innerHTML', "Ok");
  		$objResponse->assign('nameStatus', 'style.color', "#0f0");
  	}
  	else
  	{
			$objResponse->assign('nameStatus', 'innerHTML', "Der Name muss Vor- und Nachname enthalten!");
			$objResponse->assign('nameStatus', 'style.color', "#f90");
  	}
	}
	else
	{
		$objResponse->assign('nameStatus', 'innerHTML', "Der Name darf keine ungültigen Zeichen enthalten!");
		$objResponse->assign('nameStatus', 'style.color', "#f90");
	}
	
	$objResponse->assign('nameStatus', 'style.fontWeight', "bold");
	return $objResponse;
}

//Überprüft die Korrektheit des Nicks und prüft ob dieser schon vorhanden ist
function registerCheckNick($val)
{
	global $db_table, $db;
	
	$objResponse = new xajaxResponse();
	$objResponse->assign('nickStatus', 'style.fontWeight', "bold");
	if (checkValidNick($val))
	{
		if (strlen($val)>=NICK_MINLENGHT)
		{
			$res=$db->query("SELECT user_id FROM ".$db_table['users']." WHERE user_nick='$val';");
            if (mysql_num_rows($res)>0)
            {
                $objResponse->assign('nickStatus', 'innerHTML', "Dieser Benutzername wird bereits benutzt!");
                $objResponse->assign('nickStatus', 'style.color', "#f90");
            }
            else
            {
                $objResponse->assign('nickStatus', 'innerHTML', "Ok");
                $objResponse->assign('nickStatus', 'style.color', "#0f0");
            }
        }
        else
        {
            $objResponse->assign('nickStatus', 'innerHTML', "Der Benutzername ist noch zu kurz!");
            $objResponse->assign('nickStatus', 'style.color', "#f90");
        }
    }
    else
    {
    	$objResponse->assign('nickStatus', 'innerHTML', "Der Benutzername ist nicht korrekt!");
    	$objResponse->assign('nickStatus', 'style.color', "#f90");
    }
    
	return $objResponse;
}

//Überprüft die Korrektheit der Eingabe von der Email Adresse und prüft ob diese schon vorhanden ist
function registerCheckEmail($val)
{
	global $db_table, $db;
	$objResponse = new xajaxResponse();
	if (checkEmail($val))
	{
		$res=$db->query("SELECT user_id FROM ".$db_table['users']." WHERE user_email='$val' OR user_email_fix='$val';");
        if (mysql_num_rows($res)>0)
        {
            $objResponse->assign('emailStatus', 'innerHTML', "Diese E-Mail-Adresse wird bereits benutzt!");
            $objResponse->assign('emailStatus', 'style.color', "#f90");
            $objResponse->assign('emailStatus', 'style.fontWeight', "bold");
        }
        else
        {
            $objResponse->assign('emailStatus', 'innerHTML', "Ok");
            $objResponse->assign('emailStatus', 'style.color', "#0f0");
            $objResponse->assign('emailStatus', 'style.fontWeight', "bold");
        }
    }
    else
    {
        $objResponse->assign('emailStatus', 'innerHTML', "Du musst eine korrekte E-Mail-Adresse eingeben!");
        $objResponse->assign('emailStatus', 'style.color', "#f90");
        $objResponse->assign('emailStatus', 'style.fontWeight', "bold");
    }
    
	return $objResponse;
}

//Zeigt Rasseninfos an
function registerShowRace($val)
{
	global $db_table, $db;
	$res=$db->query("
		SELECT 
			* 
		FROM 
			races 
		WHERE 
			race_id='$val'
	;");
	$arr=mysql_fetch_array($res);
	$objResponse = new xajaxResponse();
	
	ob_start();
	
	echo text2html($arr['race_comment'])."<br/><br/><table class=\"tb\">";
	echo "<tr><th colspan=\"2\">St&auml;rken / Schw&auml;chen</th></tr>";
	if ($arr['race_f_metal']!=1)
	{
		echo "<tr><th>".RES_METAL."</a></td><td>".get_percent_string($arr['race_f_metal'],1)."</td></tr>";
	}
	if ($arr['race_f_crystal']!=1)
	{
		echo "<tr><th>".RES_CRYSTAL."</a></td><td>".get_percent_string($arr['race_f_crystal'],1)."</td></tr>";
	}
	if ($arr['race_f_plastic']!=1)
	{
		echo "<tr><th>".RES_PLASTIC."</a></td><td>".get_percent_string($arr['race_f_plastic'],1)."</td></tr>";
	}
	if ($arr['race_f_fuel']!=1)
	{
		echo "<tr><th>".RES_FUEL."</a></td><td>".get_percent_string($arr['race_f_fuel'],1)."</td></tr>";
	}
	if ($arr['race_f_food']!=1)
	{
		echo "<tr><th>".RES_FOOD."</a></td><td>".get_percent_string($arr['race_f_food'],1)."</td></tr>";
	}
	if ($arr['race_f_power']!=1)
	{
		echo "<tr><th>Energie</a></td><td>".get_percent_string($arr['race_f_power'],1)."</td></tr>";
	}
	if ($arr['race_f_population']!=1)
	{
		echo "<tr><th>Wachstum</a></td><td>".get_percent_string($arr['race_f_population'],1)."</td></tr>";
	}
	if ($arr['race_f_researchtime']!=1)
	{
		echo "<tr><th>Forschungszeit</a></td><td>".get_percent_string($arr['race_f_researchtime'],1,1)."</td></tr>";
	}
	if ($arr['race_f_buildtime']!=1)
	{
		echo "<tr><th>Bauzeit</a></td><td>".get_percent_string($arr['race_f_buildtime'],1,1)."</td></tr>";
	}
	if ($arr['race_f_fleettime']!=1)
	{
		echo "<tr><th>Fluggeschwindigkeit</a></td><td>".get_percent_string($arr['race_f_fleettime'],1,1)."</td></tr>";
	}
	echo  "<tr><th colspan=\"2\">Spezielle Schiffe</th></tr>";
	$res=$db->query("
	SELECT 
		ship_name,
		ship_shortcomment 
	FROM 
		ships 
	WHERE 
  	ship_race_id='".$val."' 
  	AND ship_buildable=1 
  	AND special_ship=0;");
	if (mysql_num_rows($res)>0)
	{
		while ($arr=mysql_fetch_array($res))
		{
			echo "<tr><th>".text2html($arr['ship_name'])."</th><td>".text2html($arr['ship_shortcomment'])."</td></tr>";
		}
	}
	else
		echo "<tr><td colspan=\"2\">Keine Rassenschiffe vorhanden</td></tr>";
		
	echo "</table>";
 	$objResponse->assign('raceInfo', 'innerHTML', ob_get_contents());
 	ob_end_clean();
  return $objResponse;
}




?>