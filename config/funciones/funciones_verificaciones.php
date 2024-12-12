<?php
function verificarEntradaDatosArray($campo_esperados, $metodo)
{
    $mensaje = "";
    for ($i = 0; $i < count($campo_esperados); $i++) {
        if (isset($metodo[$campo_esperados[$i]])) {
            if (empty($metodo[$campo_esperados[$i]])) {
                $mensaje = "Hay un campo que se encuentra vacio. Por favor, complete el formulario correctamente";
                return $mensaje;
            }
        } else {
            $mensaje = "No se han declarado todos los datos necesarios.";
            return $mensaje;
        }
    }
    return $mensaje;
}


function verificarEntradaDatosMatriz($campo_esperados, $metodo)
{
    $mensaje = "";
    foreach ($campo_esperados as $clave => $valor) {
        if (isset($metodo[$clave])) {
            if (empty($metodo[$clave] || $metodo[$clave] == 0)) {
                $mensaje = "Hay un campo que se encuentra vacio. Por favor, complete el formulario correctamente";
                return $mensaje;
            }
        } else {
            $mensaje = "No se han declarado todos los datos necesarios.";
            return $mensaje;
        }
    }
    return $mensaje;
}




function tieneEspaciosInicioFinCadena($cadena)
{
    return strlen(trim($cadena)) !== strlen(($cadena));
}


function listaCamposConEspacioInicioFin($lista_campos, $metodo)
{
    $camposNoValidos = [];
    foreach ($lista_campos as $clave  => $valor) {
        $campo = $metodo[$clave];
        if (tieneEspaciosInicioFinCadena($campo)) {
            array_push($camposNoValidos, $valor['label']);
        }
    }
    return $camposNoValidos;
}


function listaConLongitudNoValida($campo_esperados)
{
    $camposNoValidos = [];
    foreach ($campo_esperados as $clave => $valor) {
        if (verificarLongitudTexto($_POST[$clave], $valor['length_minimo'], $valor['length_maximo'])) {
            array_push($camposNoValidos, $valor['label']);
        }
    }
    return $camposNoValidos;
}
function verificarLongitudTexto($texto, $cant_caracteres_min, $cant_caracteres_max)
{
    return !(mb_strlen($texto) >= $cant_caracteres_min && mb_strlen($texto) <= $cant_caracteres_max);
}

function verificarLatitudLongitud($latitud, $longitud)
{
    if (!($latitud === null && $longitud === null) && !((is_float($latitud) && is_float($longitud)))) {
        throw new Exception("Los valores proporcionados para la latitud y la longitud no son validos");
    }
}


function validarCampoSelectTipoUsuario($conexion, $campo)
{
    $variable = $_POST[$campo];
    if (is_numeric($variable)) {
        $num = intval($variable);
        $tipos_usuarios = obtenerTiposUsuarios($conexion);
        for ($i = 0; $i < count($tipos_usuarios); $i++) {
            if ($tipos_usuarios[$i]['id_tipo_usuario'] == $num) {
                return $num;
            }
        }
        throw new Exception("No se recibio un paremetro valido del campo tipo de usuario.");
    } else {
        throw new Exception("No se recibio valor numerico del campo tipo de usuario.");
    }
}

function listaCamposNumFueraRango($campo_esperados)
{

    $camposNoValidos = [];
    foreach ($campo_esperados as $clave => $valor) {
        if (!($_POST[$clave] >= $valor['minimo'] &&  $_POST[$clave] <= $valor['maximo'])) {
            array_push($camposNoValidos, $valor['label']);
        }
    }
    return  $camposNoValidos;
}

function validarCampoNumericoEntero($campo_numerico)
{

    $limite = 2147483647;
    return $campo_numerico > $limite;
}


function listaCamposNumNoPositivo($campo_esperados)
{
    $camposNoValidos = [];
    foreach ($campo_esperados as $clave => $valor) {
        if ($_POST[$clave] < 0) {
            array_push($camposNoValidos, $valor['label']);
        }
    }
    return  $camposNoValidos;
}

function listaCamposNoNumerico($campo_esperados)
{
    $camposNoValidos = [];
    foreach ($campo_esperados as $clave => $valor) {
        if (!is_numeric($_POST[$clave])) {
            array_push($camposNoValidos, $valor['label']);
        }
    }
    return  $camposNoValidos;
}

function verificarSubidaArchivos($nombre)
{
    if (isset($_FILES[$nombre]) && is_array($_FILES[$nombre]['name'])) {
        $cantidad_archivos = count($_FILES[$nombre]['name']);
        for ($i = 0; $i < $cantidad_archivos; $i++) {
            if ($_FILES[$nombre]['error'][$i] !== UPLOAD_ERR_OK) {
                return false;
            }
        }
        return true;
    } else {
        return false;
    }
}

function validarCantArchivos($nombre, $cant_min, $cant_max)
{
    if (isset($_FILES[$nombre])) {
        $cantidad_archivos = count($_FILES[$nombre]['name']);
        if ($cantidad_archivos >= $cant_min && $cantidad_archivos <= $cant_max) {
            return true;
        }
    }
    return false;
}

function validarCantTotalArchivosConBD($nombre, $nombre_bd, $cant_min, $cant_max)
{
    $cantidad_archivos = (isset($_FILES[$nombre]['name'])) ? count($_FILES[$nombre]['name']) : 0;
    $cantidad_archivos_bd = (isset($_POST[$nombre_bd])) ? count($_POST[$nombre_bd]) : 0;
    $cantidad_total_archivos = $cantidad_archivos + $cantidad_archivos_bd;
    if ($cantidad_total_archivos >= $cant_min && $cantidad_total_archivos <= $cant_max) {
        return true;
    }
    return false;
}

function validarIgualdadCamposPasswordsPost($password1, $password2)
{

    if ($_POST[$password1] !== $_POST[$password2]) {
        throw new Exception("Las contraseñas ingresadas no son iguales");
    }
}

function validarCampoPassword($password)
{
    if (strpos($password, ' ')) {
        throw new Exception("La contraseña no puede tener espacios en blanco");
    }
}

function validarCampoEmail($email)
{
    $expresion_regular = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !preg_match($expresion_regular, $email)) {
        throw new Exception("Por favor ingrese un email con formato valido");
    }
}




function verificarExtensionesValidasArchivos($archivoExtensiones, $extensionesValidas)
{
    return in_array($archivoExtensiones, $extensionesValidas);
}

function validarTamanioImagen($tamanio)
{
    $limitImagen = 10 * 1024 * 1024;
    if ($tamanio <= $limitImagen) {
        return true;
    }
    return false;
}

function validarTamanioVideo($tamanio)
{
    $limitVideo = 100 * 1024 * 1024;
    if ($tamanio <= $limitVideo) {
        return true;
    }
    return false;
}

function validarCampoSelectCategoriaProducto($conexion, $campo)
{
    $variable = $_POST[$campo];
    if (is_numeric($variable)) {
        $id_categoria = intval($variable);
        if (!laCategoriaSigueDisponible($conexion, id_categoria: $id_categoria)) {
            throw new Exception("La categoria seleccionada no esta disponible actualmente. Por favor actualiza la pagina para ver las categorias disponibles o selecciona otra categoria");
        }
    } else {
        throw new Exception("No se recibio valor numerico en el campo categoria del producto.");
    }
}

function validarCampoSelectEstadoProducto($conexion, $campo)
{
    $esValido = false;
    $variable = $_POST[$campo];
    if (is_numeric($variable)) {
        $num = intval($variable);
        $estado_producto = obtenerEstadosProducto($conexion);
        for ($i = 0; $i < count($estado_producto); $i++) {
            if ($estado_producto[$i]['id_estado_producto'] == $num) {
                $esValido = true;
                break;
            }
        }
        if (!$esValido) {
            throw new Exception("No se recibio un paremetro valido del campo estado del producto.");
        }
    } else {
        throw new Exception("No se recibio valor numerico del campo estado del producto.");
    }
}
function validarCampoSelectCalificacion($select_calificacion)
{
    if ($select_calificacion != "null") {
        if (!is_nan($select_calificacion)) {
            for ($i = 0; $i <= 5; $i++) {
                if ($select_calificacion == $i) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function validarCampoSelectCalificacionProducto($select_calificacion)
{
    global $calificacion_max_productos;
    if ($select_calificacion != "null") {
        if (!is_nan($select_calificacion)) {
            for ($i = 0; $i <= $calificacion_max_productos; $i++) {
                if ($select_calificacion == $i) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    } else {
        return true;
    }
}
function validarCampoSelectCalificacionEmprendedor($select_calificacion)
{
    global $calificacion_max_emprendedores;
    if ($select_calificacion != "null") {
        if (!is_nan($select_calificacion)) {
            for ($i = 0; $i <= $calificacion_max_emprendedores; $i++) {
                if ($select_calificacion == $i) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    } else {
        return true;
    }
}