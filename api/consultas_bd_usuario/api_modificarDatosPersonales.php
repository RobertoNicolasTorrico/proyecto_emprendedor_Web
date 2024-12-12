<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");


// Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario", "tipo_usuario", "nombres", "apellidos");
$campos_modificar = [
    'nombres' => ['label' => 'Nombres', 'length_minimo' => 1, 'length_maximo' => 50],
    'apellidos' => ['label' => 'Apellidos', 'length_minimo' => 1, 'length_maximo' => 50],
];
$respuesta = [];


try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }


        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Se obtiene los datos de la solicitud POST
        $id_usuario = $_POST['id_usuario'];
        $tipo_usuario = $_POST['tipo_usuario'];
        $nombres = $_POST['nombres'];
        $apellidos = $_POST['apellidos'];

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No puede modificar los datos del usuario debido a que su cuenta no esta activa o esta baneado");
        }

        //Verifica que la longitud de los campos de textos de caracteres sea valida 
        $campos_errores = listaCamposConEspacioInicioFin($campos_modificar, $_POST);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("Los siguientes campos tienen espacios en blanco al inicio o al final: " . $errores_texto . ".");
        }

        //Verifica que la longitud de los campos de textos de caracteres sea valida 
        $campos_errores = listaConLongitudNoValida($campos_modificar);
        if (!empty($campos_errores)) {
            $errores_texto = implode(", ", $campos_errores);
            throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado: " . $errores_texto . ".");
        }

        //Se obtiene los datos del usuario por el id de usuario 
        $datos_usuario = obtenerDatosUsuarioPorIdUsuario($conexion, $id_usuario);

        //Verifica que los nuevos datos recibidos no sean iguales a los datos originales del usuario
        if ($nombres ==  $datos_usuario['nombres'] && $apellidos ==  $datos_usuario['apellidos']) {
            $estado = 'info';
            throw new Exception("No hubo cambios en los datos personales del usuario");
        }

        //Se guarda los datos modificados del usuario
        modificarNombreYApellidoUsuario($conexion, $id_usuario, $nombres, $apellidos);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'La informacion personal del usuario fue modificada correctamente';

        http_response_code(200);
    } else {
        http_response_code(405);
        throw new Exception("Metodo no permitido o datos no recibidos");
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    http_response_code(400);
    $respuesta['mensaje'] = $e->getMessage();
}
//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
