<?php
namespace FreePBX\modules\Gestionrenvois;

#[\AllowDynamicProperties]
class GestionRenvoisComponent extends \component {
    private $generated = false;
    private $redirecturl = null;

    public function generateconfigpage($loadView=null, $variables = array()) {
        if(empty($loadView)) {
            $loadView = __DIR__ . "/views/currentcomponent.php";
        }
        if($this->generated) {
            return '';
        }
        $this->generated = true;

        // Vérifier si nous sommes dans le cas "pas d'extension"
        $hasNoExtension = isset($this->guielems_no_extension);

        $html = array(
            "top" => array(),
            "middle" => array('general' => array()),
            "bottom" => array()
        );

        if ($hasNoExtension) {
            // Ajouter les éléments d'en-tête
            if (isset($this->guielems_top)) {
                foreach ($this->guielems_top as $priority => $elements) {
                    foreach ($elements as $element) {
                        $html["top"][] = $element->getRawArray();
                    }
                }
            }

            // Ajouter le message "pas d'extension"
            if (isset($this->guielems_no_extension)) {
                foreach ($this->guielems_no_extension as $priority => $elements) {
                    foreach ($elements as $element) {
                        $html["top"][] = $element->getRawArray();
                    }
                }
            }

            $vars = array(
                "tabtranslations" => array(),
                "showtabs" => false,
                "display" => isset($_REQUEST['display']) ? $_REQUEST['display'] : '',
                "active" => 'general',
                "hiddens" => array(),
                "action" => "",
                "html" => $html,
                "jsfuncs" => array()
            );

            $finalVars = !empty($variables) ? array_merge($variables, $vars) : $vars;
            return load_view($loadView, $finalVars);
        }

        // Code pour le cas avec une extension
        if (!$this->sorted_guielems) {
            $this->sortguielems();
        }

        $crossSections = array("top", "middle", "bottom");
        $hiddens = array();
        $active = 'general';

        foreach($crossSections as $pl) {
            $divide = "guielems_".$pl;
            if(!is_array($this->$divide)) {
                continue;
            }
            foreach($this->$divide as $category => $sections) {
                foreach($sections as $order => $section) {
                    if($pl == "middle") {
                        foreach($section as $key => $elements) {
                            if($key === 'placement') continue;
                            foreach($elements as $element) {
                                $data = $element->getRawArray();
                                if($data['type'] == "hidden") {
                                    $hiddens[] = $data;
                                } else {
                                    $html[$pl][$category][$key][] = $data;
                                }
                            }
                        }
                    } else {
                        $html[$pl][] = $section->getRawArray();
                    }
                }
            }
        }

        if(!empty($html['middle'])) {
            reset($html['middle']);
            $active = key($html['middle']);
        }

        $vars = array(
            "tabtranslations" => $this->tabtranslations,
            "showtabs" => count($html['middle']) > 1,
            "display" => isset($_REQUEST['display']) ? $_REQUEST['display'] : '',
            "active" => $active,
            "hiddens" => $hiddens,
            "action" => isset($this->opts['form_action']) ? $this->opts['form_action'] : "",
            "html" => $html,
            "jsfuncs" => isset($this->jsfuncs) ? $this->jsfuncs : array()
        );

        $finalVars = !empty($variables) ? array_merge($variables, $vars) : $vars;
        return load_view($loadView, $finalVars);
    }
}