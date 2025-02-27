<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

// Installation
function gestionrenvois_install() {
    // Votre code d'installation
}

// Mise à jour
function gestionrenvois_upgrade($oldversion) {
    // Gérer les mises à jour de version
    switch($oldversion) {
        case '16.0.0':
            // Mise à jour vers 17.0.0
            break;
    }
    return true;
}

// Désinstallation
function gestionrenvois_uninstall() {
    // Votre code de désinstallation
}