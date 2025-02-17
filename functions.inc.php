<?php 
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

function gestionrenvois_getmodulevalue($id) {
    global $db;
    $sql = "select value from gestionrenvois_module where id = ?";
    $results = $db->getAll($sql, array($id)); // Utilisation de requêtes préparées
    if(DB::IsError($results)) 
        $results = null;
    return isset($results[0][0]) ? $results[0][0] : null;
}

function gestionrenvois_setmodulevalue($id, $value) {
    global $db;
    $sql = "update gestionrenvois_module set value = ? where id = ?";
    $db->query($sql, array($value, $id)); // Utilisation de requêtes préparées
}

function gestionrenvois_hookGet_config($engine) {
    global $db;
    global $ext;
    switch($engine) {
        case 'asterisk':
            // Implementation si nécessaire
        break;
    }
}

function gestionrenvois_getextensions() {
    $tmparray = gestionrenvois_getcoreuserslist(true);
    return $tmparray ?? array();
}

function gestionrenvois_getfeatureslist($exten) {
    global $db;
    $sql = "SELECT description, 
            COALESCE(customcode, defaultcode) as featurecode,
            enabled, 
            gestionrenvois_features_list.modulename,
            gestionrenvois_features_list.featurename,
            type,
            dbfamily,
            dbkey,
            value_on,
            value_off,
            display_on,
            display_off
            FROM gestionrenvois_features_list 
            INNER JOIN featurecodes 
            ON gestionrenvois_features_list.modulename = featurecodes.modulename 
            AND gestionrenvois_features_list.featurename = featurecodes.featurename";
    
    $results = $db->getAll($sql);
    if(DB::IsError($results)) 
        return array();
        
    $tmparray = array();
    	foreach ($results as $val) {
			if ($exten) {		
				$state = gestionrenvois_getfeaturestate ($exten, $val[6], $val[7]);
			}
			else
			{
				$state = '';
			}
		$tmparray[] = array($state, $val[0], $val[1], $val[2], $val[3], $val[4], $val[5], $val[6], $val[7], $val[8], $val[9], $val[10], $val[11]);
		}
	return $tmparray;
}

function gestionrenvois_getextenfeaturesstates($extenlist) {
    global $db;
    if (!is_array($extenlist)) {
        return array();
    }
    
    $sql = "SELECT type, dbfamily, dbkey, value_on, value_off, extenlistdisplay_on, extenlistdisplay_off 
            FROM gestionrenvois_features_list 
            INNER JOIN featurecodes 
            ON gestionrenvois_features_list.modulename = featurecodes.modulename 
            AND gestionrenvois_features_list.featurename = featurecodes.featurename";
    
    $results = $db->getAll($sql);
    if(DB::IsError($results)) {
        return $extenlist;
    }

    $tmparray = array();
    $tmparray2 = array();

			foreach ($results as $val) {
				$featurestatus = $val;
				foreach ($extenlist as $exten) {
					if ($exten[0]) {
						$state = gestionrenvois_getfeaturestate ($exten[0], $featurestatus [1], $featurestatus [2]);
					}
					else
					{
						$state = '';
					}
					$featuretype = $featurestatus[0];
					$value_on = str_replace('{EXTEN}',$exten[0],$featurestatus[3]);
					$value_off = str_replace('{EXTEN}',$exten[0],$featurestatus[4]);
					$display_on = $featurestatus[5];
					$display_off = $featurestatus[6];
            
            
            switch ($featuretype) {
                case 'EXTEN':

                    if (empty($state) || $state == $value_off) {
                        $display = $display_off;
                    } elseif ($state == $value_on) {
                        $display = str_replace('{VALUE}', $state, $display_on);
                    } else {
                        $display = str_replace('{VALUE}', $state, $display_on);
							}
						break;
						case 'ONOFF':
							if (($state == '') || ($state == $value_off)) {
								$display= $display_off;
							}
							elseif ($state == $value_on) {
								$display = str_replace('{VALUE}',$state,$display_on);
							}
							else
							{
								$display = str_replace('{VALUE}',$state,$display_on);
							}
						break;
					}
            if (!empty($display)) {
                $tmparray[$exten[0]][] = $display;
            }
        }
    }

    foreach ($extenlist as $exten) {
        if (!isset($exten[0])) continue;
        
        $extendisplay = '';
        if (isset($tmparray[$exten[0]]) && is_array($tmparray[$exten[0]])) {
            $extendisplay = implode('&nbsp;', $tmparray[$exten[0]]);
        }
        $extendisplay = empty($extendisplay) ? $exten[1] : $extendisplay.'<BR>'.$exten[1];
        $tmparray2[] = array($exten[0], $extendisplay);
    }

    return !empty($tmparray2) ? $tmparray2 : $extenlist;
}

function gestionrenvois_getfeaturestate($exten, $family, $key) {
    global $astman;
    if (!$astman) {
        return '';
    }
    
    $family = str_replace('{EXTEN}', $exten, $family);
    $key = str_replace('{EXTEN}', $exten, $key);
    
    $ret = $astman->database_get($family, $key);
    return $ret !== false ? $ret : '';
}

function gestionrenvois_getcoreuserslist($showfeature = false) {
    $tmparray = core_users_list();
    if ($tmparray && $showfeature) {
        $tmparray = gestionrenvois_getextenfeaturesstates($tmparray);
    }
    return $tmparray ?? array();
}

function gestionrenvois_get_config($engine) {
    global $ext;
    switch($engine) {
        case 'asterisk':
            // Implementation si nécessaire
        break;
    }
}

//gestionrenvois page helper
//we are using gui styles so there is very little on the page
//
function gestionrenvois_configpageinit($dispnum) {
global $currentcomponent;
	switch ($dispnum) {
		case 'gestionrenvois':
			$currentcomponent->addguifunc('gestionrenvois_configpageload');
			$currentcomponent->addprocessfunc('gestionrenvois_configprocess', 5);  
		break;
	}
}

function gestionrenvois_configpageload() {
global $currentcomponent;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$action= isset($_REQUEST['action'])?$_REQUEST['action']:null;
	if ($action == 'all') {
//we may decide to display all at some point
	}
	else
	{
//need to get page name/type dynamically
		if (!$extdisplay) {
//maybe display all at some point
			$currentcomponent->addguielem('_top', new gui_pageheading('title', _(gestionrenvois_getmodulevalue('moduledisplayname')), false), 0);
			$currentcomponent->addguielem('_top', new gui_subheading('subtitle', "Merci de selectionner une extention!", false), 0);
		}
		else
		{
			$currentcomponent->addguielem('_top', new gui_hidden('extdisplay', $extdisplay));
			$currentcomponent->addguielem('_top', new gui_pageheading('title', _(gestionrenvois_getmodulevalue('moduledisplayname')), false), 0);
			$currentcomponent->addguielem('_top', new gui_subheading('subtitle', "Extension: ".$extdisplay, false), 0);
			$extlist = gestionrenvois_getcoreuserslist(false);
			$featurelist = gestionrenvois_getfeatureslist($extdisplay);
			foreach ($featurelist as $featurestatus) {
				$state = $featurestatus [0];
				$description = $featurestatus [1];
				$featurecode = $featurestatus [2];
				$enabled = $featurestatus [3];
				$modulename = $featurestatus [4];
				$featurename = $featurestatus [5];
				$featuretype = $featurestatus [6];
				$family = str_replace('{EXTEN}',$extdisplay,$featurestatus [7]);
				$key = str_replace('{EXTEN}',$extdisplay,$featurestatus [8]);
				$value_on = str_replace('{EXTEN}',$extdisplay,$featurestatus [9]);
				$value_off = str_replace('{EXTEN}',$extdisplay,$featurestatus [10]);
				$display_on = str_replace('{EXTEN}',$extdisplay,$featurestatus [11]);
				$display_off = str_replace('{EXTEN}',$extdisplay,$featurestatus [12]);
				switch ($featuretype) {
					case 'EXTEN':
						if (($state == '') || ($state == $value_off)) {
							$display = $display_off;
						}
						elseif ($state == $value_on) {
							$display = str_replace('{VALUE}',$state,$display_on);
						}
						else
						{
							$display = str_replace('{VALUE}',$state,$display_on);
						}
//						$display = '<strong>'.$display.'</strong>';
						$currentcomponent->addoptlistitem($modulename.'|'.$featurename, $value_off, '~~Desactiver~~');
						$currentcomponent->addoptlistitem($modulename.'|'.$featurename, 'enterexten', '~~AutreNumero~~');
						foreach ($extlist as $val) {
							$currentcomponent->addoptlistitem($modulename.'|'.$featurename, str_replace('{VALUE}',$val[0],$value_on), $val[1]);
						}
						$currentcomponent->setoptlistopts($modulename.'|'.$featurename, 'sort', false);
						$currentcomponent->addguielem('Features', new gui_selectbox('features['.$family.'|'.$key.']', $currentcomponent->getoptlist($modulename.'|'.$featurename), '', $description.': '.$display, '',true,'javascript:ctl = document.frm_gestionrenvois[\''.'features['.$family.'|'.$key.']'.'\']; idx = ctl.selectedIndex; opt = ctl.options[idx]; if(opt.value == \'enterexten\') {newnum = prompt(\'Entrer le Numero\',\'\'); if(newnum) {ctl.options[idx] = new Option(newnum,newnum); ctl.selectedIndex=idx;} else {ctl.selectedIndex = null}}; '));
                        
					break;
					case 'ONOFF':
						if (($state == '') || ($state == $value_off)) {
							$display= $display_off;
						}
						elseif ($state == $value_on) {
							$display= $display_on;
						}
						else
						{
							$display= 'Unknown';
						}
//						$display = '<strong>'.$display.'</strong>';
						$currentcomponent->addoptlistitem($modulename.'|'.$featurename, $value_on, $display_on);
						$currentcomponent->addoptlistitem($modulename.'|'.$featurename, $value_off, $display_off);
//						$currentcomponent->setoptlistopts($modulename.'|'.$featurename, 'sort', false);

						$currentcomponent->addguielem('Features', new gui_selectbox('features['.$family.'|'.$key.']', $currentcomponent->getoptlist($modulename.'|'.$featurename), '', $description.': '.$display, '', true));
					break;
				}
			}
		}
		$currentcomponent->addguielem('_bottom', new gui_link('link', _(gestionrenvois_getmodulevalue('moduledisplayname').' v'.gestionrenvois_getmodulevalue('moduleversion')), 'http://www.famillecattan.com', true, false), 9);
	}
       $currentcomponent->addguielem('_top', new gui_hidden('action', ($extdisplay ? 'edit' : 'add')));
}

function gestionrenvois_configprocess() {
    $action = $_REQUEST['action'] ?? null;
    $ext = $_REQUEST['extdisplay'] ?? null;
    $features = $_REQUEST['features'] ?? array();
    
    global $astman;
    if (!$astman) {
        return;
    }

    switch ($action) {
        case 'edit':
            foreach ($features as $key => $val) {
                $famkey = explode('|', $key, 2);
                if (count($famkey) === 2) {
                    $family = $famkey[0];
                    $key = $famkey[1];
                    if (!empty($val)) {
                        if ($val === '{DELETE}') {
                            $astman->database_del($family, $key);
                        } else {
                            $astman->database_put($family, $key, $val);              
                        }
                    }
                }
            }
            break;
    }
}

//update a single feature from the gestionrenvois page
function gestionrenvois_edit() {

}

?>