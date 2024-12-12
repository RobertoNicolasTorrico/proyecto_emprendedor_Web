<?php

//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/consultas_bd/consultas_categoria.php");
include("../../../config/funciones/funciones_verificaciones.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/config_define.php");


//Limite de caracteres para la descripcion 
$categoria_datos = [
    'cant_min' => 1,
    'cant_max' => 40,
];


// Campos esperados en la solicitud POST
$campo_esperados = ['id_categoria', 'txt_tipo_categoria'];
$estado = 'danger';

try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario administrador
        if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
            throw new Exception("Debe iniciar sesion para poder modificar la informacion de una categoria");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No puede modificar la informacion de la categoria por que no es usuario administrador valido");
        }


        //Se obtiene los datos de la solicitud POST
        $id_categoria = $_POST['id_categoria'];
        $txt_tipo_categoria = $_POST['txt_tipo_categoria'];


        //Verifica si la categoria sigue disponible
        if (!laCategoriaSigueDisponible($conexion, $id_categoria)) {
            throw new Exception("Esta categoria fue eliminada previamente. Por favor actualiza la pagina para ver los cambios");
        }


        //Se obtiene los datos de la categoria por el id de la categoria
        $categoria = obtenerCategoriaId($conexion, $id_categoria);


        //Verifica que  la nueva categoria ingresada no sea igual a la categoria anterior 
        if ($txt_tipo_categoria == $categoria['nombre_categoria']) {
            $estado = 'info';
            throw new Exception("No hubo cambios en la categoria");
        }

        //Verifica si la categoria ingresada aun no existe en el sistema
        if (verificarSiTipoCategoriaEstaDisponible($conexion, $txt_tipo_categoria)) {
            $estado = 'info';
            throw new Exception("Ya hay otra categoria con el nombre ingresado");
        }

        //Verifica que la categoria no tenga espacios al inicio o al final de la cadena
        if (tieneEspaciosInicioFinCadena($txt_tipo_categoria)) {
            throw new Exception("La categoria no puede tener espacios en blanco al inicio o al final");
        }


        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($txt_tipo_categoria, $categoria_datos['cant_min'], $categoria_datos['cant_max'])) {
            throw new Exception("El campo categoria debe tener entre 1 y 40 caracteres");
        }

        //Se guarda las modificaciones de la categoria
        modificarNombreCategoria($conexion, $txt_tipo_categoria, $id_categoria);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'La informacion de la categoria fue modificada correctamente';
    } else {
        throw new Exception("No se recibio una solicitud POST.");
    }
} catch (Exception $e) {

    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['mensaje'] = $e->getMessage();
    $respuesta['estado'] = $estado;
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
