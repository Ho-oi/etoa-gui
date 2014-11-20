<?PHP 
    $tpl->setView("config/editor");
    $tpl->assign("subtitle", 'Erweiterte Konfiguration');
    
    if (isset($_POST['submit']))
    {
      foreach ($cfg->categories() as $ck => $cv) {      
        foreach ($cfg->itemInCategory($ck) as $i)
        {
          $v = isset($i->v) ? create_sql_value((string)$i->v['type'],(string)$i['name'],"v",$_POST) : "";
          $p1 = isset($i->p1) ? create_sql_value((string)$i->p1['type'],(string)$i['name'],"p1",$_POST) : "";
          $p2 = isset($i->p2) ? create_sql_value((string)$i->p2['type'],(string)$i['name'],"p2",$_POST) : "";
          $cfg->add((string)$i['name'],$v,$p1,$p2);
        }
      }
	  BackendMessage::reloadConfig();
      $tpl->assign('msg', "&Auml;nderungen wurden &uuml;bernommen!");
      $tpl->assign('msg_type', "ok");
 			$tpl->assign('activeTab', $_POST['activeTab']);
    }

    $configData = array();
    foreach ($cfg->categories() as $ck => $cv) {
      $configData[$ck]['name'] = $cv;
      $items = array();
      foreach ($cfg->itemInCategory($ck) as $i)
      {
        if (isset($i->v))
        {
          $items[] = array(
            'label' => $i->v['comment'],
            'name' => $i['name'],
            'type' => 'v',
            'field' => display_field((string)$i->v['type'], (string)$i['name'], "v"),
          );
        }
        if (isset($i->p1))
        {
          $items[] = array(
            'label' => $i->p1['comment'],
            'name' => $i['name'],
            'type' => 'p1',
            'field' => display_field((string)$i->p1['type'], (string)$i['name'], "p1"),
          );				
        }
        if (isset($i->p2))
        {
          $items[] = array(
            'label' => $i->p2['comment'],
            'name' => $i['name'],
            'type' => 'p2',
            'field' => display_field((string)$i->p2['type'], (string)$i['name'], "p2"),
          );				
        }
      }        
      $configData[$ck]['items'] = $items;
    }

    $tpl->assign("configData", $configData);
?>