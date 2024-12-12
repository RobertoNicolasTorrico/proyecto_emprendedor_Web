<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_notificacion.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_session.php");
include("../../config/config_define.php");



$id_url = isset($_GET['id']) ? $_GET['id'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

$tipo_usuario = 0;
try {

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();


    //Verificar los datos de sesion del usuario
    $usuario_inicio_sesion = verificarEntradaDatosSession(['id_usuario', 'tipo_usuario']);
    if ($usuario_inicio_sesion) {

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        //Verifica si el usuario es valido
        $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);
        if ($usuario_valido) {

            if ($tipo_usuario == 2) {
                //Se obtiene los datos del emprendedor en caso que sea uno
                $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
                if (!empty($usuario_emprendedor)) {
                    //Las variables se utilizan para que el usuario emprendedor pueda ir a su perfil desde el navbar
                    $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
                    $id_usuario_emprendedor_token = hash_hmac('sha1', $id_usuario_emprendedor, KEY_TOKEN);
                }
            }
        }
    }



    //Se verifica que los campos de la URL no esten vacios
    if ($id_url != '' && $token != '') {
        $token_id_valido = validarIdTokenEmail($conexion, $id_url, $token);

        //Se verifica que el usuario haya querido cambiar de email
        if ($token_id_valido) {

            //Se obtiene el nuevo email a cambiar 
            $email_usuario = obtenerNuevoEmailUsuario($conexion, $id_url);

            //Se modifica el email del usuario
            modificarEmailUsuario($conexion, $id_url, $email_usuario);
            $mensaje = "El email registrado en la cuenta del usuario fue cambiado exitosamente";
            $estado = "success";
        } else {
            throw new Exception("El token o el id no son validos");
        }
    } else {
        throw new Exception("El campo id o token estan vacios");
    }
} catch (Exception $e) {
    // Capturar cualquier excepción y guardar el mensaje de error
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
    <title>Proyecto Emprendedor Cambiar email</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">



</head>

<body>


    <?php

    //Se obtiene el tipo de navbar que se va usar dependiendo del usuario
    switch ($tipo_usuario) {
        case 1:
            include($url_navbar_usuario_comun);
            break;
        case 2:
            include($url_navbar_usuario_emprendedor);
            break;
        default:
            include($url_navbar_general);
            break;
    }
    ?>


    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>

        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container">
            <div class="alert alert-<?php echo ($estado); ?>" role="alert">
                <?php echo ($mensaje); ?>
            </div>
        </div>
    </main>
    <!-- Incluye el pie de pagina y el script necesario para el funcionamiento de la pagina.-->
    <?php require("../../template/footer.php"); ?>
    <script src="../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>