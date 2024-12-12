<?php

//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_producto.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_categoria.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/config_define.php");


// Campos esperados en la solicitud POST
$campo_esperados = [
    'id_producto' => ['label' => 'Codigo del producto'],
    'nombre_producto' => ['label' => 'Nombre del producto'],
    'descripcion' => ['label' => 'Descripcion del producto'],
    'precio' => ['label' => 'Precio'],
    'stock' => ['label' => 'Stock'],
    'categoria_producto' => ['label' => 'Categoria del producto'],
    'estado_producto' => ['label' => 'Estados del producto'],
];

// Campos esperados en la solicitud POST con formato de texto
$campo_esperados_text = [
    'nombre_producto' => ['label' => 'Nombre del producto', 'length_minimo' => 1, 'length_maximo' => 80],
    'descripcion' => ['label' => 'Descripcion del producto', 'length_minimo' => 1, 'length_maximo' => 1000],
];

// Campos esperados en la solicitud POST con formato numerico
$campo_esperados_num = [
    'stock' => ['label' => 'Stock', 'minimo' => 0],
    'precio' => ['label' => 'Precio', 'minimo' => 0],
];

//Configuracion de archivos permitidos
$archivo = [
    'cant_min' => 1,
    'cant_max' => 5,
    'extenciones' => ['png', 'jpeg', 'jpg'],
];

$respuesta['lista'] = "";
$respuesta["mensaje"] = "";
$respuesta["estado"] = "";
$campos_errores = [];
$estado = 'danger';


try {

    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosMatriz($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario
        if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
            throw new Exception("Debe iniciar sesion para poder modificar la publicacion");
        }

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION['id_usuario'];
        $tipo_usuario = $_SESSION['tipo_usuario'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede modificar la informacion del productos debido a que su cuenta no esta activa o esta baneado");
        }

        //Verifica que el usuario sea un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No se puede guardar la informacion del producto ya que no es un usuario emprendedor");
        }

        //Se obtiene los datos del usuario emprendedor por el id del usuario
        $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
        if (empty($usuario_emprendedor)) {
            throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
        }
        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];


        //Obtener los datos de la solicitud POST
        $id_producto = $_POST['id_producto'];
        $nombre_producto = $_POST['nombre_producto'];
        $descripcion = htmlspecialchars($_POST['descripcion']);
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $categoria_producto = $_POST['categoria_producto'];
        $estado_producto = $_POST['estado_producto'];
        $nombres_files_bd = (isset($_POST["nombres_files_bd"])) ? $_POST["nombres_files_bd"] :  array();
        $files = (isset($_FILES['files'])) ? $_FILES['files'] :  array();




        //Verifica si producto existe
        if (!verificarSiElProductoExiste($conexion, $id_producto)) {
            throw new Exception("El producto ya fue eliminado previamente, por lo que no puede ser modificado. Por favor retrocede a la página anterior para ver los cambios");
        }


        //Se obtiene los datos de la publicacion del producto por el id del producto y el id de usuario emprendedor
        $producto = obtenerProductoDelUsuarioEmprendedor($conexion, $id_producto, $id_usuario_emprendedor);
        if (empty($producto)) {
            throw new Exception("No se pudo obtener la informacion de la publicacion. por favor intente mas tarde");
        }


        //Se obtiene los datos de las imagenes de la publicacion del producto por id del producto
        $imagenes_productos = obtenerListaImgProducto($conexion, $id_producto);
        if (empty($imagenes_productos)) {
            throw new Exception("No se pudo obtener los datos necesarios para modificar el producto");
        }


        // Extrae los nombres de los archivos de la publicación en un array
        $nombre_imagenes = array_column($imagenes_productos, 'nombre_archivo');

        // Compara la lista actual de archivos con la recibida por el usuario
        $imagenes_eliminadas = array_diff($nombre_imagenes, $nombres_files_bd);


        // Reindexa el array resultante para asegurar que los índices sean consecutivos
        $imagenes_eliminadas = array_values($imagenes_eliminadas);


        //Se obtiene el nombre de la carpeta donde se guardan los archivos de la publicacion
        $nombre_carpeta =  $imagenes_productos[0]['nombre_carpeta'];



        //Verifica que los nuevos datos recibidos no sean iguales a la publicacion original
        if (
            $nombre_producto ==  $producto['nombre_producto'] &&
            $descripcion ==  $producto['descripcion'] &&
            $precio ==  $producto['precio'] &&
            $stock ==  $producto['stock'] &&
            $categoria_producto ==  $producto['id_categoria_producto'] &&
            $estado_producto ==  $producto['id_estado_producto'] &&
            empty($imagenes_eliminadas) &&
            empty($files)
        ) {
            $estado = 'info';
            throw new Exception("No hubo cambios en los datos del producto");
        }


        //Verifica que la cantidad de total de archivos eliminados y los nuevos archivos sea valido
        if (!validarCantTotalArchivosConBD('files', 'nombres_files_bd', $archivo['cant_min'], $archivo['cant_max'])) {
            throw new Exception("La cantidad de imagenes no cumplen con los requisitos.Debe ser al menos " . $archivo['cant_min'] . " imagen y como máximo " . $archivo['cant_max'] . " imagenes");
        }


        //Verifica que la categoria elegida sea valida
        validarCampoSelectCategoriaProducto($conexion, 'categoria_producto');

        //Verifica que el estado del producto elegida sea valida
        validarCampoSelectEstadoProducto($conexion, 'estado_producto');


        //Verifica que el campo numerico del stock sea valido
        if (validarCampoNumericoEntero($stock)) {
            throw new Exception("El stock maximo que puede tener un producto es 2147483647");
        }


        //Verifica que los campos de texto no tenga espacios al inicio o al final de la cadena
        $campos_errores = listaCamposConEspacioInicioFin($campo_esperados, $_POST);
        if (!empty($campos_errores)) {
            throw new Exception("No se permite que los campos tengan espacios en blanco al inicio o al final. Los siguientes campos no cumplen con esto:");
        }


        //Verifica que la longitud de los campos de textos de caracteres sea valida 
        $campos_errores = listaConLongitudNoValida($campo_esperados_text);
        if (!empty($campos_errores)) {
            throw new Exception("Los siguientes campos no cumplen con la longitud mínima de 1 carácter o el máximo de caracteres indicado:");
        }

        //Verifica que los campos numericos sea valores numericos 
        $campos_errores = listaCamposNoNumerico($campo_esperados_num);
        if (!empty($campos_errores)) {
            throw new Exception("Solo se permite ingresar valores numericos en los siguientes campos:");
        }

        //Verifica que los campos numericos sea valores numericos positivos
        $campos_errores = listaCamposNumNoPositivo($campo_esperados_num);
        if (!empty($campos_errores)) {
            throw new Exception("Los valores ingresados en los siguientes campos estan fuera de rango numerico permitido:");
        }

        //Verifica y maneja la carga de archivos en caso que se reciba uno 
        if (isset($_FILES['files'])) {

            //Verifica que la subida de archivos por parte del usuario sea correcta
            if (!verificarSubidaArchivos('files')) {
                throw new Exception("Error al subir los nuevo archivos.");
            }
            $cantidad_archivos = count($_FILES['files']['name']);
            for ($i = 0; $i < $cantidad_archivos; $i++) {

                //Se verifica que la extension del archivo sea uno valido
                $extencion_archivo = pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION);
                if (!verificarExtensionesValidasArchivos($extencion_archivo, $archivo['extenciones'])) {
                    throw new Exception(message: "El formato del archivo " . $_FILES['files']['name'][$i] . " no es valido. Formatos permitidos: JPEG, JPG y PNG");
                }
                //Se verifica que la imagen tenga un tamaño valido
                $tamanio_archivo = $_FILES['files']['size'][$i];
                if (!validarTamanioImagen($tamanio_archivo)) {
                    throw new Exception("La imagen " . $_FILES['files']['name'][$i] . " excede el tamaño maximo permitido de 10MB");
                }
            }
        }

        //Se guarda las modificaciones de la publicacion en la cuenta usuario emprendedor
        modificarPublicacionProducto($conexion, $nombre_producto, $descripcion, $precio, $stock, $estado_producto, $categoria_producto, $id_usuario_emprendedor, $id_producto, $files, $imagenes_eliminadas, $nombre_carpeta);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'La informacion del producto fue modificada correctamente';
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
