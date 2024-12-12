<?php
function obtenerCondicionWhereBuscadorEmprendedor($campo_buscar, $campo_calificacion)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['u.nombre_usuario', 'up.nombre_emprendimiento'], $campo_buscar);

    if ($campo_calificacion !== "todos_calificacion") {
        if ($campo_calificacion === "sin_calificacion") {
            $condicion_where .= obtenerCondicionWhereIsNull('up.calificacion_emprendedor');
        } else {
            if (is_numeric($campo_calificacion)) {
                $condicion_where .= obtenerCondicionWhereIgual('up.calificacion_emprendedor', $campo_calificacion);
            }
        }
    }
    return $condicion_where;
}

function obtenerCondicionASCDESCFiltroEmprendedor($num_ordenamiento)
{
    $condicion_ASC_DESC = "";
    switch ($num_ordenamiento) {
        case 1:
            $condicion_ASC_DESC = "u.fecha DESC";
            break;
        case 2:
            $condicion_ASC_DESC = "u.fecha ASC";
            break;
    }
    return $condicion_ASC_DESC;
}

function obtenerCondicionWhereBuscadorEmprendedorSeguidos($campo_buscar)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['u.nombre_usuario', 'up.nombre_emprendimiento'], $campo_buscar);
    return $condicion_where;
}

