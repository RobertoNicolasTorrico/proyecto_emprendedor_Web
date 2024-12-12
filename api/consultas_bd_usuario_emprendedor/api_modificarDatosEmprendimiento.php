<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/config_define.php");


//Limite de caracteres para la descripcion 
$descripcion_datos = [
    'cant_min' => 1,
    'cant_max' => 150,
];

//Configuracion de archivos permitidos
$archivo_extensiones_imagen =  ['png', 'jpeg', 'jpg'];

//Se obtiene los datos de la solicitud POST
$descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";
$file = (isset($_FILES['file'])) ? $_FILES['file'] : "";


//Campos esperados en la solicitud POST
$campo_esperados = array("id_usuario", "tipo_usuario");


$respuesta = [];


try {
    //Verifica si la solicitud es POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No puede modificar los datos del usuario debido a que su cuenta no esta activa o esta baneado");
        }


        //Verifica que la descripcion del usuario este vacia
        if (!empty($descripcion)) {
            //Verifica que la descripcion no tenga espacios al inicio o al final de la cadena
            if (tieneEspaciosInicioFinCadena($descripcion)) {
                throw new Exception("El campo descripcion no puede tener espacios en blanco al inicio o al final");
            }

            //Verifica que la longitud de caracteres sea valida 
            if (verificarLongitudTexto($descripcion, $descripcion_datos['cant_min'], $descripcion_datos['cant_max'])) {
                throw new Exception("El campo descripcion debe tener entre 1 y 150 caracteres");
            }
        }

        //Verifica y maneja la carga de archivos en caso que se reciba uno o se halla eliminado un archivo
        if (!empty($file)) {

            //Verifica que la subida de archivo por parte del usuario sea correcta
            if (verificarSubidaArchivos('file')) {
                throw new Exception("Error al subir archivos.");
            }

            $extencion_archivo = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $tamanio_archivo = $_FILES['file']['size'];

            //Se verifica si la extension del archivo sea un imagen
            if (verificarExtensionesValidasArchivos($extencion_archivo, $archivo_extensiones_imagen)) {

                //Se verifica que la imagen tenga un tamaño valido
                if (!validarTamanioImagen($tamanio_archivo)) {
                    throw new Exception("La imagen " . $_FILES['files']['name'] . " excede el tamaño maximo permitido de 10MB");
                }
            } else {
                throw new Exception("El formato del archivo " . $_FILES['files']['name'] . " no es valido. Formatos permitidos: JPEG, JPG y PNG");

            }
        }



        //Se obtiene los datos del emprendimiento por el ID del usuario
        $datos_usuario = obtenerDatosUsuarioYUsuarioEmprendedorPorIdUsuario($conexion, $id_usuario);
        $id_usuario_emprendedor = $datos_usuario['id_usuario_emprendedor'];
        $foto_perfil_actual = $datos_usuario['foto_perfil_nombre'];

        //Verifica que los nuevos datos recibidos no sean iguales a la descripcion del emprendedor
        if ($descripcion == $datos_usuario['descripcion'] && empty($file)) {
            throw new Exception("No hubo cambios en los datos del empredimiento");
        }

        //Se guarda las modificaciones del emprendimiento de la cuenta del usuario
        modificarDatosEmprendedor($conexion, $id_usuario_emprendedor, $descripcion, $file, $foto_perfil_actual);
        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'La informacion del empredimiento fue modificada correctamente';

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
