<?php
function obtenerCondicionWhereBuscadorUsuarioSeguidor($campo_buscar)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['u.nombre_usuario'], $campo_buscar);

    return $condicion_where;
}

function obtenerCondicionWhereBuscadorUsuario($campo_buscar, $campo_fecha, $campo_tipo_usuario, $campo_estado_usuario, $campo_calificacion)
{
    $condicion_where = "";

    if (!empty($campo_buscar) || !empty($campo_fecha) || !empty($campo_tipo_usuario) || $campo_estado_usuario != "todos" || $campo_calificacion != "todos") {
        $condicion_where .= "WHERE";
    }

    $condicion_where_buscador = obtenerCondicionWhereBuscador(['u.nombre_usuario', 'u.email', 'up.nombre_emprendimiento'], $campo_buscar);
    if (!empty($condicion_where_buscador)) {
        $condicion_where .= substr($condicion_where_buscador, 4);
    }

    $condicion_where_fecha = obtenerCondicionWhereFecha('fecha', $campo_fecha);
    if (empty($condicion_where_buscador)) {
        $condicion_where .= substr($condicion_where_fecha, 4);
    } else {
        $condicion_where .=  $condicion_where_fecha;
    }

    $condicion_where_tipo_usuario =  obtenerCondicionWhereIgual('id_tipo_usuario', $campo_tipo_usuario);
    if (empty($condicion_where_buscador) && empty($condicion_where_fecha)) {
        $condicion_where .= substr($condicion_where_tipo_usuario, 4);
    } else {
        $condicion_where .=  $condicion_where_tipo_usuario;
    }

    switch ($campo_estado_usuario) {
        case "activado":
            $condicion_where_estado_usuario =  obtenerCondicionWhereIgual('activado', "1");

            break;
        case "no_activado":
            $condicion_where_estado_usuario =  obtenerCondicionWhereIgual('activado', "0");

            break;
        case "baneado":
            $condicion_where_estado_usuario =  obtenerCondicionWhereIgual('baneado', "1");

            break;
        case "no_baneado":
            $condicion_where_estado_usuario =  obtenerCondicionWhereIgual('baneado', "0");
            break;
        default:
            $condicion_where_estado_usuario = "";
            break;
    }

    if (empty($condicion_where_buscador) && empty($condicion_where_fecha) && empty($condicion_where_tipo_usuario)) {
        $condicion_where .= substr($condicion_where_estado_usuario, 4);
    } else {
        $condicion_where .=  $condicion_where_estado_usuario;
    }


    $condicion_where_calificacion = "";
    if ($campo_calificacion === "sin_calificacion") {
        $condicion_where_calificacion = obtenerCondicionWhereIsNull('up.calificacion_emprendedor');
        $condicion_where_calificacion .=  obtenerCondicionWhereIgual('id_tipo_usuario', "2");
    } else {
        if (is_numeric($campo_calificacion)) {
            $condicion_where_calificacion = obtenerCondicionWhereIgual('up.calificacion_emprendedor', $campo_calificacion);
            $condicion_where_calificacion .=  obtenerCondicionWhereIgual('id_tipo_usuario', "2");
        }
    }

    if (empty($condicion_where_tipo_usuario) && empty($condicion_where_buscador) && empty($condicion_where_fecha) && empty($condicion_where_estado_usuario)) {
        $condicion_where .= substr($condicion_where_calificacion, 4);
    } else {
        $condicion_where .=  $condicion_where_calificacion;
    }

    return $condicion_where;
}
