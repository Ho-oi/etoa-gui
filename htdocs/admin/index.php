<?PHP
//////////////////////////////////////////////////////
// The Andromeda-Project-Browsergame                //
// Ein Massive-Multiplayer-Online-Spiel             //
// Programmiert von Nicolas Perrenoud<mail@nicu.ch> //
// als Maturaarbeit '04 am Gymnasium Oberaargau	    //
//////////////////////////////////////////////////////
// $Id$
//////////////////////////////////////////////////////

ob_start();

require("inc/includer.inc.php");

// Create template object
$tpl = TemplateEngine::getInstance();

$tpl->setLayout("admin/default_main");
$tpl->setView("admin/default");

$tpl->assign("css_theme", (!isset($themePath) || !is_file(RELATIVE_ROOT."/web/css/themes/admin/".$themePath."css")) ? "default" : $themePath);
$tpl->assign("page_title", getGameIdentifier()." Administration");
$tpl->assign("ajax_js", $xajax->printJavascript(XAJAX_DIR));

initTT();

// Login if requested
if (isset($_POST['login_submit']))
{
	if (! $s->login($_POST))
	{
		include("inc/admin_login.inc.php");
	}
}

// Perform logout if requested
if (isset($_GET['logout']) && $_GET['logout']!=null)
{
	$s->logout();
	forward('.',"Logout");
}

// Validate session
if (!$s->validate())
{
	include("inc/admin_login.inc.php");
}
else
{
	// Load admin user data
	$cu = new AdminUser($s->user_id);

	// Zwischenablage
	if (isset($_GET['cbclose']))
	{
		$s->clipboard = null;
	}
	$cb = isset ($s->clipboard) && $s->clipboard==1 ? true : false;
	
	
	$tpl->assign("search_query",(isset($_POST['search_query']) ? $_POST['search_query'] : '' ));
	$tpl->assign("user_level",$cu->level);

	$navmenu = fetchJsonConfig("admin-menu.conf");
	$tpl->assign("navmenu",$navmenu);
	
	$tpl->assign("page",$page);
	$tpl->assign("sub", $sub);
	$tpl->assign("time", time());

	$tpl->assign('is_unix', UNIX);		
	
	$nres = dbquery("select COUNT(*) from admin_notes where admin_id='".$s->user_id."'");
	$narr = mysql_fetch_row($nres);
	$tpl->assign("num_notes", $narr[0]);
	$tpl->assign("num_tickets", Ticket::countAssigned($s->user_id) + Ticket::countNew());

	$tpl->assign("current_user_nick", $cu->nick);	
	
	// Status widget
	$tpl->assign("is_unix", UNIX);
	if (UNIX) {
		$tpl->assign("eventhandler_pid", checkDaemonRunning($cfg->daemon_pidfile));
		intval(exec("cat /proc/cpuinfo | grep processor | wc -l", $out));
		$load = sys_getloadavg();
		$tpl->assign("sys_load", round($load[2]/intval($out[0])*100, 2) );
	}
	
	$ures=dbquery("SELECT count(*) FROM users;");
	$uarr=mysql_fetch_row($ures);

	$gres=dbquery("SELECT COUNT(*) FROM user_sessions WHERE time_action>".(time() - $cfg->user_timeout->v).";");
	$garr=mysql_fetch_row($gres);

	$a1res=dbquery("SELECT COUNT(*)  FROM admin_user_sessions WHERE time_action>".(time() - $cfg->admin_timeout->v).";");
	$a1arr=mysql_fetch_row($a1res);

	$tpl->assign("users_online", $garr[0]);
	$tpl->assign("users_count", $uarr[0]);
	$tpl->assign("users_allowed", $cfg->enable_register->p2);
	$tpl->assign("admins_online", $a1arr[0]);
	$tpl->assign("admins_count", AdminUser::countAll());
	$tpl->assign("db_size", DBManager::getInstance()->getDbSize());
	
	$tpl->assign("side_nav_widgets", $tpl->getChunk("admin/status_widget"));
			
	// Inhalt einbinden
	if (isset($_GET['adminlist']))
	{
		require("inc/adminlist.inc.php");
	}
	elseif (isset($_GET['myprofile']))
	{
		require("inc/myprofile.inc.php");
	}
	else
	{
		// Activate update system
		if (isset($_GET['activateupdate']) && $_GET['activateupdate']==1)
		{
			Config::getInstance()->set("update_enabled",1);
		}

		if (Config::getInstance()->update_enabled->v !=1 )
		{
			echo "<br/>";
			iBoxStart("Updates deaktiviert");
			echo "Die Updates sind momentan deaktiviert!";
			echo " <a href=\"?page=$page&amp;activateupdate=1\">Aktivieren</a>";
			iBoxEnd();
		}

		// Check permissions
		$allow_inc=false;
		$found = false;
		foreach ($navmenu as $cat=> $item) 	{
			if ($item['page']==$page && $sub=="") {
				$found = true;
				if ($item['level'] <= $cu->level) {
					$allow_inc = true;
				}
			} else if (isset($item['children'])) {
				foreach ($item['children'] as $title=> $data) {
					if ($item['page']==$page && $data['sub']==$sub) {
						$found = true;
						if ($data['level'] <= $cu->level) {
							$allow_inc = true;
						}
					}
				}
			}
		}
		if ($allow_inc || !$found)	{
			if (preg_match('^[a-z\_]+$^',$page)  && strlen($page)<=50) {
				$contentFile = "content/".$page.".php";
				if (is_file($contentFile)) {
					include($contentFile);
					logAccess($page,"admin",$sub);
				} else {
					cms_err_msg("Die Seite $page wurde nicht gefunden!");
				}
			} else {
				echo "<h1>Fehler</h1>Der Seitenname <b>".$page."</b> enth&auml;lt unerlaubte Zeichen!<br><br><a href=\"javascript:history.back();\">Zur&uuml;ck</a>";
			}
		} else {
			echo "<h1>Kein Zugriff</h1> Du hast keinen Zugriff auf diese Seite!";
		}
	}
	
	// Write all changes of $s to the session variable
	$_SESSION[SESSION_NAME]=$s;
	dbclose();

	$tpl->assign("content_overflow", ob_get_clean());
	$render_time = explode(" ",microtime());

	$tpl->assign("render_time",round($render_time[1]+$render_time[0]-$render_starttime,3));

	$tpl->render();
}
?>