<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

// Constantes pour le module
class GestionRenvoisConstants {
    const TYPE_EXTEN = 'EXTEN';
    const TYPE_ONOFF = 'ONOFF';
    const DEFAULT_DISPLAY = 'Unknown';
    const VERSION = '17.0.1';
}

// Autoloader pour la classe personnalisée
spl_autoload_register(function ($class) {
    $prefix = 'FreePBX\\modules\\gestionrenvois\\';
    $base_dir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.class.php';

    if (file_exists($file)) {
        require $file;
    }
});

function gestionrenvois_getmodulevalue($id) {
    if (empty($id)) {
        return null;
    }
    global $db;
    $sql = "select value from gestionrenvois_module where id = ?";
    try {
        $results = $db->getAll($sql, array($id));
        if(DB::IsError($results)) {
            error_log("gestionrenvois: Erreur lors de la récupération de la valeur du module");
            return null;
        }
        return isset($results[0][0]) ? $results[0][0] : null;
    } catch (Exception $e) {
        error_log("gestionrenvois: Exception: " . $e->getMessage());
        return null;
    }
}

function gestionrenvois_setmodulevalue($id, $value) {
    if (empty($id)) {
        return false;
    }
    global $db;
    try {
        $sql = "update gestionrenvois_module set value = ? where id = ?";
        $result = $db->query($sql, array($value, $id));
        return !DB::IsError($result);
    } catch (Exception $e) {
        error_log("gestionrenvois: Exception lors de la mise à jour: " . $e->getMessage());
        return false;
    }
}

function gestionrenvois_getextensions() {
    return gestionrenvois_getcoreuserslist(true) ?? array();
}

function gestionrenvois_getfeatureslist($exten) {
    if (empty($exten)) {
        return array();
    }
    
    global $db;
    $sql = "SELECT 
            description, 
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
    
    try {
        $results = $db->getAll($sql);
        if(DB::IsError($results)) {
            error_log("gestionrenvois: Erreur lors de la récupération des features");
            return array();
        }
        
        return array_map(function($val) use ($exten) {
            $state = gestionrenvois_getfeaturestate($exten, $val[6], $val[7]);
            return array($state, $val[0], $val[1], $val[2], $val[3], $val[4], $val[5], 
                        $val[6], $val[7], $val[8], $val[9], $val[10], $val[11]);
        }, $results);
    } catch (Exception $e) {
        error_log("gestionrenvois: Exception: " . $e->getMessage());
        return array();
    }
}

function gestionrenvois_getextenfeaturesstates($extenlist) {
    if (!is_array($extenlist)) {
        return array();
    }
    
    global $db;
    $sql = "SELECT type, dbfamily, dbkey, value_on, value_off, extenlistdisplay_on, extenlistdisplay_off 
            FROM gestionrenvois_features_list 
            INNER JOIN featurecodes 
            ON gestionrenvois_features_list.modulename = featurecodes.modulename 
            AND gestionrenvois_features_list.featurename = featurecodes.featurename";
    
    try {
        $results = $db->getAll($sql);
        if(DB::IsError($results)) {
            return $extenlist;
        }

        $tmparray = array();
        $tmparray2 = array();

        foreach ($results as $val) {
            foreach ($extenlist as $exten) {
                if (empty($exten[0])) {
                    continue;
                }

                $state = gestionrenvois_getfeaturestate($exten[0], $val[1], $val[2]);
                $value_on = str_replace('{EXTEN}', $exten[0], $val[3]);
                $value_off = str_replace('{EXTEN}', $exten[0], $val[4]);
                
                $display = '';
                switch ($val[0]) { // type
                    case GestionRenvoisConstants::TYPE_EXTEN:
                    case GestionRenvoisConstants::TYPE_ONOFF:
                        if (empty($state) || $state === $value_off) {
                            $display = $val[6]; // display_off
                        } else {
                            $display = str_replace('{VALUE}', $state, $val[5]); // display_on
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
            
            $extendisplay = isset($tmparray[$exten[0]]) && is_array($tmparray[$exten[0]]) 
                ? implode('&nbsp;', $tmparray[$exten[0]]) 
                : '';
            $extendisplay = empty($extendisplay) ? $exten[1] : $extendisplay.'<BR>'.$exten[1];
            $tmparray2[] = array($exten[0], $extendisplay);
        }

        return !empty($tmparray2) ? $tmparray2 : $extenlist;
    } catch (Exception $e) {
        error_log("gestionrenvois: Exception: " . $e->getMessage());
        return $extenlist;
    }
}

function gestionrenvois_getfeaturestate($exten, $family, $key) {
    global $astman;
    if (!$astman || empty($exten) || empty($family) || empty($key)) {
        error_log("gestionrenvois: Asterisk Manager n'est pas disponible ou paramètres invalides");
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

function gestionrenvois_configpageinit($dispnum) {
    global $currentcomponent;
    if ($dispnum === 'gestionrenvois') {
        $currentcomponent->addguifunc('gestionrenvois_configpageload');
        $currentcomponent->addprocessfunc('gestionrenvois_configprocess', 5);
    }
}

function gestionrenvois_configpageload() {
    global $currentcomponent;
    $extdisplay = $_REQUEST['extdisplay'] ?? null;
    $action = $_REQUEST['action'] ?? null;

    // Afficher le titre du module
    $moduleDisplayName = gestionrenvois_getmodulevalue('moduledisplayname');
    $currentcomponent->addguielem('_top', new gui_pageheading('title', _($moduleDisplayName), false), 0);

    if (!$extdisplay) {
        // Ajouter le message dans une section spéciale pour le cas sans extension
        $currentcomponent->addguielem('no_extension', new gui_subheading('subtitle', _("Merci de sélectionner une extension!"), false), 0);
        return;
    }

    // Si on a une extension, on continue avec le code normal
    $currentcomponent->addguielem('_top', new gui_hidden('extdisplay', $extdisplay));
    $currentcomponent->addguielem('_top', new gui_subheading('subtitle', sprintf(_("Extension: %s"), $extdisplay), false), 0);

    $extlist = gestionrenvois_getcoreuserslist(false);
    $featurelist = gestionrenvois_getfeatureslist($extdisplay);

    foreach ($featurelist as $featurestatus) {
        list($state, $description, $featurecode, $enabled, $modulename, $featurename,
             $featuretype, $family, $key, $value_on, $value_off, $display_on, $display_off) = $featurestatus;

        $family = str_replace('{EXTEN}', $extdisplay, $family);
        $key = str_replace('{EXTEN}', $extdisplay, $key);
        $value_on = str_replace('{EXTEN}', $extdisplay, $value_on);
        $value_off = str_replace('{EXTEN}', $extdisplay, $value_off);


        $display = '';
        switch ($featuretype) {
            case GestionRenvoisConstants::TYPE_EXTEN:
                if (empty($state) || $state === $value_off) {
                    $display = $display_off;
                } else {
                    $display = str_replace('{VALUE}', $state, $display_on);
                }

                $currentcomponent->addoptlistitem("$modulename|$featurename", $value_off, _('~~Désactiver~~'));
                $currentcomponent->addoptlistitem("$modulename|$featurename", 'enterexten', _('~~Autre Numéro~~'));
                
                foreach ($extlist as $val) {
                    $currentcomponent->addoptlistitem(
                        "$modulename|$featurename",
                        str_replace('{VALUE}', $val[0], $value_on),
                        $val[1]
                    );
                }

                $currentcomponent->setoptlistopts("$modulename|$featurename", 'sort', false);
                $currentcomponent->addguielem('Features', 
                    new gui_selectbox(
                        "features[$family|$key]",
                        $currentcomponent->getoptlist("$modulename|$featurename"),
                        '',
                        "$description: $display",
                        '',
                        true,
                        'javascript:ctl = document.frm_gestionrenvois[\'features['.$family.'|'.$key.']\'];' .
                        'idx = ctl.selectedIndex; opt = ctl.options[idx];' .
                        'if(opt.value == \'enterexten\') {' .
                        '    newnum = prompt(\'Entrer le Numéro\',\'\');' .
                        '    if(newnum) {' .
                        '        ctl.options[idx] = new Option(newnum,newnum);' .
                        '        ctl.selectedIndex=idx;' .
                        '    } else {' .
                        '        ctl.selectedIndex = null' .
                        '    }' .
                        '};'
                    )
                );
                break;

            case GestionRenvoisConstants::TYPE_ONOFF:
                $display = empty($state) || $state === $value_off 
                    ? $display_off 
                    : ($state === $value_on ? $display_on : GestionRenvoisConstants::DEFAULT_DISPLAY);

                $currentcomponent->addoptlistitem("$modulename|$featurename", $value_on, $display_on);
                $currentcomponent->addoptlistitem("$modulename|$featurename", $value_off, $display_off);
                
                $currentcomponent->addguielem('Features',
                    new gui_selectbox(
                        "features[$family|$key]",
                        $currentcomponent->getoptlist("$modulename|$featurename"),
                        '',
                        "$description: $display",
                        '',
                        true
                    )
                );
                break;
        }
    }

    // Ajout du lien de version en bas de page
    $moduleVersion = gestionrenvois_getmodulevalue('moduleversion');
    $currentcomponent->addguielem('_bottom',
        new gui_link(
            'link',
            _("$moduleDisplayName v$moduleVersion"),
            'http://www.famillecattan.com',
            true,
            false
        ),
        9
    );

    $currentcomponent->addguielem('_top', new gui_hidden('action', $extdisplay ? 'edit' : 'add'));
}

function gestionrenvois_configprocess() {
    $action = $_REQUEST['action'] ?? null;
    if ($action !== 'edit') return;

    $features = $_REQUEST['features'] ?? array();
    global $astman;
    if (!$astman) {
        error_log("gestionrenvois: Asterisk Manager n'est pas disponible");
        return;
    }

    try {
        foreach ($features as $key => $val) {
            $famkey = explode('|', $key, 2);
            if (count($famkey) !== 2) continue;

            list($family, $key) = $famkey;
            if (empty($val)) continue;

            if ($val === '{DELETE}') {
                $astman->database_del($family, $key);
            } else {
                $astman->database_put($family, $key, $val);
            }
        }
        return true;
    } catch (Exception $e) {
        error_log("gestionrenvois: Erreur lors du traitement des features: " . $e->getMessage());
        return false;
    }
}

function gestionrenvois_hookGet_config($engine) {
    switch($engine) {
        case 'asterisk':
            // Implementation future si nécessaire
            break;
    }
}

function gestionrenvois_get_config($engine) {
    switch($engine) {
        case 'asterisk':
            // Implementation future si nécessaire
            break;
    }
}