<?php
/*
Plugin Name: Ubicaciones
Description: Provee un tipo de post que permite mostrar diferentes lugares con descripciones en el mapa, a partir de direcciones o coordenadas.
Author: Elías Dorigoni
Version: 1.0
Author URI: http://www.eliasdorigoni.com/
*/

require 'class.frontend.php';
require 'class.backend.php';

if (is_admin()) {
    new BackendUbicaciones;
} else {
    new FrontendUbicaciones;
}
