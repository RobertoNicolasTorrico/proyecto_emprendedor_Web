<?php
//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_categoria.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/funciones/funciones_verificaciones.php");
include("../../../config/funciones/funciones_generales.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/funciones/funciones_token.php");
include("../../../config/config_define.php");


// Campos esperados en la solicitud POST
$campo_esperados = array("id_categoria_eliminar");

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
        $id_categoria = $_POST['id_categoria_eliminar'];

        //Valida que el campo oculto solo contenga numeros
        if (!is_numeric($id_categoria)) {
            throw new Exception("Los campos ocultos solo deben contener numeros");
        }

        //Verifica si la categoria sigue disponible
        if (!laCategoriaSigueDisponible($conexion, $id_categoria)) {
            throw new Exception("Esta categoria fue eliminada previamente. Por favor actualiza la pagina para ver los cambios");
        }

        //Se obtiene la cantidad de productos que esten registrados en la categoria
        $cantidad = obtenerCantidadProductosDeCategoria($conexion, $id_categoria);

        //Verifica que la categoria no tenga productos 
        if ($cantidad > 0) {
            throw new Exception("No se puede eliminar la categoria seleccionada por que tiene productos registrados");
        }

        //Se elimina la categoria del producto
        bajaCategoriaProducto($conexion, $id_categoria);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'La categoria fue eliminada correctamente';
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
