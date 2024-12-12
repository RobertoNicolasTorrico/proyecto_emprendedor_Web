<?php

//Archivos de configuracion y funciones necesarias
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");

$respuesta['lista'] = "";
$respuesta["mensaje"] = "";
$respuesta["estado"] = "";
$campos_errores = [];
$estado = 'danger';


// Campos esperados en la solicitud POST
$campo_esperados = [
    'password_nueva' => ['label' => 'Nueva contraseña', 'length_minimo' => 6, 'length_maximo' => 60],
    'password_nueva_confirmacion' => ['label' => 'Confirmar nueva contraseña', 'length_minimo' => 6, 'length_maximo' => 60]
];

$token = isset($_GET['token']) ? $_GET['token'] : '';
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';

try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {


        //Verifica que si el  token y el de id de usuario no esten vacios
        if (empty($id_usuario)  || empty($token)) {
            throw new Exception("El campo id o token esta vacio");
        }

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosMatriz($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
        $campos_errores = listaCamposConEspacioInicioFin($campo_esperados, $_POST);
        if (!empty($campos_errores)) {
            throw new Exception("No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:");
        }

         //Verifica que la longitud de caracteres sea valida 
         $campos_errores = listaConLongitudNoValida($campo_esperados);
         if (!empty($campos_errores)) {
             throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 6 carácter o el máximo de caracteres indicado:");
         }


        //Verifica que el nuevo password no tenga espacios al inicio o al final de la cadena
        validarCampoPassword($_POST['password_nueva']);

        //Verifica que la nueva contraseña sea igual a la confirmacion de la nueva contraseña 
        validarIgualdadCamposPasswordsPost('password_nueva', 'password_nueva_confirmacion');


        //Obtener los datos de la solicitud POST
        $password_nueva = $_POST['password_nueva'];


        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el token es valido
        if (!validarTokenNuevaPassword($conexion, $token, $id_usuario)) {
            throw new Exception("El id o token no son validos");
        }

        //Se obtiene los datos del usuario por el id del usuario
        $datos_usuario = obtenerDatosUsuarioPorIdUsuario($conexion, $id_usuario);
        $email =  $datos_usuario['email'];

        //Verifica si la cuenta del usuario es valido por email
        if (!verificarSiEsUsuarioValidoPorEmail($conexion, $email)) {
            throw new Exception("No se puede modificar la contraseña del usuario debido a que la cuenta no esta activa o esta baneado");
        }

        //Convierte la nueva contraseña en un formato cifrado y seguro. 
        $nueva_password = password_hash($password_nueva, PASSWORD_DEFAULT);

        //Modificar la contraseña actual del usuario por la nueva contraseña ingresada
        modificarPasswordOlvidadoUsuario($conexion, $nueva_password, $token, $id_usuario);

        $respuesta['mensaje'] = "Tu contraseña se ha restablecido correctamente.Seras redirigido automaticamente a la pagina de inicio de sesion para acceder a tu cuenta con tu nueva contraseña.";
        $respuesta['estado'] = 'success';
    } else {
        throw new Exception("No se recibio una solicitud POST.");
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['mensaje'] = $e->getMessage();
    $respuesta['lista'] = $campos_errores;
    $respuesta["estado"] = $estado;
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
