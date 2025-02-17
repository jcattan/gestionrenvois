<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $currentcomponent;

$dispnum = 'gestionrenvois';
$contexts = gestionrenvois_getextensions();

echo '<div class="rnav">';
drawListMenu($contexts, $skip, $type, $display, $extdisplay, _("Extension"));
echo '</div>';