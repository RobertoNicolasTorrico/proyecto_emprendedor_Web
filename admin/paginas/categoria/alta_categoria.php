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


//Limite de caracteres para la categoria 
$categoria_datos = [
    'cant_min' => 1,
    'cant_max' => 40,
];



// Campos esperados en la solicitud POST
$campo_esperados = array("txt_nueva_tipo_categoria");

$respuesta = [];

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
            throw new Exception("Debe iniciar sesion para poder agregar una nueva categoria");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede agregar una nueva categoria por que no es usuario administrador valido");
        }

        //Se obtiene los datos de la solicitud POST
        $txt_nueva_tipo_categoria = $_POST['txt_nueva_tipo_categoria'];

        //Verifica si la nueva categoria ingresada aun no existe en el sistema
        if (verificarSiTipoCategoriaEstaDisponible($conexion, $txt_nueva_tipo_categoria)) {
            $estado = 'info';
            throw new Exception("Ya hay otra categoria con el nombre ingresado");
        }

        //Verifica que la categoria no tenga espacios al inicio o al final de la cadena
        if (tieneEspaciosInicioFinCadena($txt_nueva_tipo_categoria)) {
            throw new Exception("La categoria no puede tener espacios en blanco al inicio o al final");
        }

        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($txt_nueva_tipo_categoria, $categoria_datos['cant_min'], $categoria_datos['cant_max'])) {
            throw new Exception("El campo categoria debe tener entre 1 y 40 caracteres");
        }

        //Guarda la categoria 
        altaCategoriaProducto($conexion, $txt_nueva_tipo_categoria);


        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se agrego una nueva categoria para los productos';
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
