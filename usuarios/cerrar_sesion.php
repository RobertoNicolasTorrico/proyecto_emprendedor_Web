<?php
//Establecer la sesion
session_start();

//Elimina las variables de sesion especificas del usuario que son ID del usuario y el tipo de usuario.
unset($_SESSION['id_usuario']);
unset($_SESSION['tipo_usuario']);

//Redirigir al usuario a la pagina de inicio
header("Location:../index.php");
