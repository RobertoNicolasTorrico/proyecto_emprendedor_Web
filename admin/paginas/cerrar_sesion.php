<?php

//Establecer la sesion
session_start();

//Elimina las variables de sesion especificas del usuario administrador que son ID del usuario del administrador y el tipo de usuario administrador.
unset($_SESSION['id_usuario_administrador']);
unset($_SESSION['tipo_usuario_admin']);

//Redirigir al usuario a la pagina de inicio
header("Location:iniciar_sesion/pagina_iniciar_sesion_admin.php");
