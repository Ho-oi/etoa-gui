<?PHP

// Load template engine
$tpl = new TemplateEngine();

$tpl->setView('install');
$tpl->setLayout('empty');

$tpl->assign("gameTitle", "Setup");
$tpl->assign("templateDir","designs/official/Revolution");

$tpl->assign('title', 'EtoA Installation');
$tpl->assign('version', getAppVersion());

if (!isset($_SESSION)) {
    session_start();
}
	
if (!isset($_SESSION['INSTALL'])) {
	$_SESSION['INSTALL'] = array();
}

if (!configFileExists(DBManager::getInstance()->getConfigFile()))
{
	ob_start();
	
    $steps = [
        1 => 'MySQL-Datenbank',
        2 => 'Allgemeine Daten',
        3 => 'Abschluss'
    ];
	
	if (isset($_POST['install_check']))
	{
		$_SESSION['INSTALL']['db_server'] = $_POST['db_server'];
		$_SESSION['INSTALL']['db_name'] = $_POST['db_name'];
		$_SESSION['INSTALL']['db_user'] = $_POST['db_user'];
		$_SESSION['INSTALL']['db_password'] = $_POST['db_password'];

		
		if ($_POST['db_server'] != "" && $_POST['db_name'] != "" && $_POST['db_user'] != "" && $_POST['db_password'] != "")
		{
			$dbCfg = array(
				'host' => $_SESSION['INSTALL']['db_server'],
				'dbname' => $_SESSION['INSTALL']['db_name'],
				'user' => $_SESSION['INSTALL']['db_user'],
				'password' => $_SESSION['INSTALL']['db_password'],
			);			
			if (DBManager::getInstance()->connect(0, $dbCfg))
			{
				$tpl->assign('msg', 'Datenbankverbindung erfolgreich!');
				
				$_SESSION['INSTALL']['step']=2;
				$step = 2;
			}
			else
			{
				$tpl->assign('errmsg', 'Verbindung fehlgeschlagen! Fehler: '.mysql_error());
				$_SESSION['INSTALL']['step']=1;
				$step = 1;
			}
		}
		else
		{
			$tpl->assign('errmsg', 'Achtung! Du hast nicht alle Felder ausgef&uuml;lt!');
		}
	}
	elseif(isset($_POST['step2_submit']) && $_POST['step2_submit'])
	{
		$step = 2;

		$_SESSION['INSTALL']['round_name'] = $_POST['round_name'];
		$_SESSION['INSTALL']['round_url'] = $_POST['round_url'];
		$_SESSION['INSTALL']['loginserver_url'] = $_POST['loginserver_url'];
		$_SESSION['INSTALL']['referers'] = $_POST['referers'];

		if ($_POST['round_name'] != "")
		{
			$step = 3;
			$_SESSION['INSTALL']['step'] = 3;
			
		}
		else
		{
			$tpl->assign('errmsg', 'Achtung! Du hast nicht alle Felder ausgef&uuml;lt!');
		}		
	}	
	
	if (isset($_SESSION['INSTALL']['step']) && isset($_GET['step']) && $_GET['step']>0)
	{
		$step = $_GET['step'];
	}
	else
	{
		$step = isset($_SESSION['INSTALL']['step']) ? $_SESSION['INSTALL']['step'] : 1;
	}

	$navElems = array();
	for ($i=1; $i <= count($steps); $i++) {
		if ($i <= $step) {
			$navElems[$steps[$i]] = '?step='.$i;
		} else {
			$navElems[$steps[$i]] = null;
		}
	}
	$tpl->assign('installMenu', $navElems);
	
	if ($step==3)
	{
		$dbCfg = array(
			'host' => $_SESSION['INSTALL']['db_server'],
			'dbname' => $_SESSION['INSTALL']['db_name'],
			'user' => $_SESSION['INSTALL']['db_user'],
			'password' => $_SESSION['INSTALL']['db_password'],
		);			
		DBManager::getInstance()->connect(0, $dbCfg);

		$dbConfigSting = json_encode($dbCfg, JSON_PRETTY_PRINT);
		
		$dbConfigStingEventHandler = "[mysql]
host = ".$dbCfg['host']."
database = ".$dbCfg['dbname']."
user = ".$dbCfg['user']."
password = ".$dbCfg['password']."
";
		$cfg = Config::getInstance();
		$cfg->set("referers",$_SESSION['INSTALL']['referers']);
		$cfg->set("roundname",$_SESSION['INSTALL']['round_name']);
		$cfg->set("roundurl",$_SESSION['INSTALL']['round_url']);
		$cfg->set("loginurl",$_SESSION['INSTALL']['loginserver_url']);

		writeConfigFile(DBManager::getInstance()->getConfigFile(), $dbConfigSting);
		writeConfigFile(EVENTHANDLER_CONFIG_FILE_NAME, $dbConfigStingEventHandler);
		
		$tpl->assign('msg', 'Konfiguration gespeichert!');
		
		if (!configFileExists(DBManager::getInstance()->getConfigFile()))
		{
			echo "<p>Du musst nun den folgenden Inhalt in eine neue Textdatei namens <b>".getConfigFilePath(DBManager::getInstance()->getConfigFile())."</b> speichern:</p>
			<pre class=\"code\">".$dbConfigSting."</pre><br /><br />";
		} else {
			$_SESSION['INSTALL']['step'] = 1;
		}

		if (!configFileExists(EVENTHANDLER_CONFIG_FILE_NAME)) {
			echo "<p>Für den Eventhandler musst du noch den folgenden Inhalt in eine Konfigurationsdatei <b>".getConfigFilePath(EVENTHANDLER_CONFIG_FILE_NAME)."</b> speichern:</p>
			<pre class=\"code\">".$bConfigStingEventHandler."</pre>";
		}

		echo "<p><input type=\"button\" onclick=\"document.location='admin'\" value=\"Zum Admin-Login\"/> &nbsp; 
		<input type=\"button\" onclick=\"document.location='".getLoginUrl()."'\" value=\"Zum Loginserver\"/></p>";
	
	}		
	
	
	elseif ($step==2)
	{
		$dbCfg = array(
			'host' => $_SESSION['INSTALL']['db_server'],
			'dbname' => $_SESSION['INSTALL']['db_name'],
			'user' => $_SESSION['INSTALL']['db_user'],
			'password' => $_SESSION['INSTALL']['db_password'],
		);			
		DBManager::getInstance()->connect(0, $dbCfg);		
		
		// Migrate database
		$cnt = DBManager::getInstance()->migrate();
		if ($cnt > 0) {
			$tpl->assign('msg', "Datenbank migriert");
			
			// Load config defaults
			Config::restoreDefaults();
			Config::getInstance()->reload();			
		}
		$cfg = Config::getInstance();

		if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST']))
		{
			$default_round_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
			$default_referers = $default_round_url."\n".INSTALLER_DEFAULT_LOGINSERVER_URL;
		}
		else
		{
			$default_round_url = $cfg->get('roundurl');
			$default_referers = $cfg->get('referers');
		}

		$str = "<form action=\"?\" method=\"post\">
		<div>
			<table>
				<tr>
					<th>Name der Runde:</th>
					<td><input type=\"text\" name=\"round_name\" value=\"".(isset($_SESSION['INSTALL']['round_name']) ? $_SESSION['INSTALL']['round_name'] : 'Runde X')."\" /></td>
					<td>(z.b. Runde 1)</td>
				</tr>
				<tr>
					<th>Basis-URL der Runde:</th>
					<td><input type=\"text\" name=\"round_url\" value=\"".(isset($_SESSION['INSTALL']['round_url']) ? $_SESSION['INSTALL']['round_url'] : $default_round_url)."\" /></td>
					<td>(z.b. '.$default_round_url.')</td>
				</tr>
				<tr>
					<th>Loginserver-URL:</th>
					<td><input type=\"text\" name=\"loginserver_url\" value=\"".(isset($_SESSION['INSTALL']['loginserver_url']) ? $_SESSION['INSTALL']['loginserver_url'] : INSTALLER_DEFAULT_LOGINSERVER_URL)."\" /></td>
					<td>(z.b. ".INSTALLER_DEFAULT_LOGINSERVER_URL.", leerlassen für lokales Login)</td>
				</tr>
				<tr>
					<th>Referers:</th>
					<td><textarea name=\"referers\" rows=\"6\" cols=\"50\">".(isset($_SESSION['INSTALL']['referers']) ? $_SESSION['INSTALL']['referers'] : $default_referers)."</textarea><br/>
					(alle Seiten, welche als Absender gelten sollen. Also der Loginserver, sowie der aktuelle Server. Mache für jeden Eintrag eine neue Linie!)</td>
					<td></td>
				</tr>
			</table>
		</div>
		<p><input type=\"submit\" name=\"step2_submit\" value=\"Weiter\" /></p>";
		$tpl->assign('installform', $str);
	}	
	else
	{
		$str = "<div>
			<table>
				<tr>
					<th>Server:</th>
					<td><input type=\"text\" name=\"db_server\" value=\"".(isset($_SESSION['INSTALL']['db_server']) ? $_SESSION['INSTALL']['db_server'] : 'localhost')."\" autocomplete=\"off\" /></td>
					<td>(z.b. localhost)</td>
				</tr>
				<tr>
					<th>Datenbank:</th>
					<td><input type=\"text\" name=\"db_name\" value=\"".(isset($_SESSION['INSTALL']['db_name']) ? $_SESSION['INSTALL']['db_name'] : 'etoa_roundx')."\" autocomplete=\"off\" /></td>
					<td>(z.b. etoaroundx)</td>
				</tr>
				<tr>
					<th>User:</th>
					<td><input type=\"text\" name=\"db_user\" value=\"".(isset($_SESSION['INSTALL']['db_user']) ? $_SESSION['INSTALL']['db_user'] : 'etoa_roundx')."\" autocomplete=\"off\" /></td>
					<td>(z.b. etoauser)</td>
				</tr>
				<tr>
					<th>Passwort:</th>
					<td><input type=\"password\" name=\"db_password\" value=\"".(isset($_SESSION['INSTALL']['db_password']) ? $_SESSION['INSTALL']['db_password'] : '')."\" autocomplete=\"off\" /></td>
					<td>(mind. 10 Zeichen)</td>
				</tr>
			</table>
		</div>
		<p><input type=\"submit\" name=\"install_check\" value=\"Eingaben prüfen\" /></p>";	
        $tpl->assign('installform', $str);
	}
	$tpl->assign('content', ob_get_clean());
}
else
{
	$tpl->assign('errmsg', "Ihre Konfigurationsdatei existiert bereits!");
}

$tpl->render();

?>
