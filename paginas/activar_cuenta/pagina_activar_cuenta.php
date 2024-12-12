<?php
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_token.php");
include("../../config/config_define.php");
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';


//Establecer la sesion
session_start();


//Elimina las variables de sesion especificas del usuario que son ID del usuario y el tipo de usuario.
unset($_SESSION['id_usuario']);
unset($_SESSION['tipo_usuario']);

$estado = "";
try {

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Se verifica que la URL tenga el ID de un usuario y el token
    if (!empty($id_usuario) && !empty($token)) {

        //Se verifica que el usuario si halla pedido activar su cuenta
        $token_id_valido = validarIdToken($conexion, $id_usuario, $token);
        if ($token_id_valido) {
            //Se activa la cuenta del usuario
            activarCuentaUsuario($conexion, $id_usuario);
            $mensaje = "Su cuenta ha sido activada correctamente ya puede iniciar sesion";
            $estado = "success";
        } else {
            throw new Exception("El id o token no son validos");
        }
    } else {
        throw new Exception("El campo id o token esta vacio");
    }
} catch (Exception $e) {
    $mensaje = $e->getMessage();
    $estado = "danger";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Titulo de la página-->
    <title>Proyecto Emprendedor Activar Cuenta</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <!--Incluye el archivo de la barra de navegación general.-->
    <?php include($url_navbar_general); ?>

    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>

        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container">
            <div class="alert alert-<?php echo ($estado); ?>" role="alert">
                <?php echo ($mensaje); ?>
            </div>
        </div>

    </main>

    <!-- Incluye el pie de pagina y varios scripts necesarios para el funcionamiento de la pagina.-->
    <?php require("../../template/footer.php"); ?>
    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>