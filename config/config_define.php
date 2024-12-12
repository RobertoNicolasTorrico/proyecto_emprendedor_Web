<?php

define("KEY_TOKEN", "APR.wqc-354*");


$url_base = "http://" . $_SERVER['HTTP_HOST'] . "/proyecto_emprendedor_web";
$url_usuario_emprendedor = "http://" . $_SERVER['HTTP_HOST'] . "/proyecto_emprendedor_web/usuarios/usuario_emprendedor";
$url_usuario = "http://" . $_SERVER['HTTP_HOST'] . "/proyecto_emprendedor_web/usuarios";
$url_usuario_admin = "http://" . $_SERVER['HTTP_HOST'] . "/proyecto_emprendedor_web/admin";


$url_navbar_usuario_comun = $_SERVER['DOCUMENT_ROOT'] . "/proyecto_emprendedor_web/usuarios/usuario_comun/template/navbar.php";
$url_navbar_usuario_emprendedor = $_SERVER['DOCUMENT_ROOT'] . "/proyecto_emprendedor_web/usuarios/usuario_emprendedor/template/navbar.php";
$url_navbar_usuario_admin = $_SERVER['DOCUMENT_ROOT'] . "/proyecto_emprendedor_web/admin/template/navbar.php";
$url_navbar_general = $_SERVER['DOCUMENT_ROOT'] . "/proyecto_emprendedor_web/template/navbar.php";


$url_base_archivos = "http://" . $_SERVER['HTTP_HOST'] . "/proyecto_emprendedor_web";
$url_base_guardar_archivos = $_SERVER['DOCUMENT_ROOT'] . "/proyecto_emprendedor_web";

$url_foto_perfil_predeterminada = "http://" . $_SERVER['HTTP_HOST'] . "/proyecto_emprendedor_web/img/foto_perfil/foto_de_perfil_predeterminado.jpg";
$url_logo = "http://" . $_SERVER['HTTP_HOST'] . "/proyecto_emprendedor_web/img/logo/logo.png";

$calificacion_max_productos = 5;
$calificacion_max_emprendedores = 5;

date_default_timezone_set('America/Argentina/Buenos_Aires');