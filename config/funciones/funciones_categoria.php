<?php

function obtenerCondicionWhereBuscadorCategoria($campo_buscar)
{

    $condicion_where = "";
    if (!empty($campo_buscar)) {
        $condicion_where .= "WHERE";
    }
    $condicion_where_buscador =  obtenerCondicionWhereBuscador(['cp.nombre_categoria'], $campo_buscar);
    if (!empty($condicion_where_buscador)) {
        $condicion_where .= substr($condicion_where_buscador, 4);
    }

    return $condicion_where;
}

function obtenerCondicionWhereBuscadorCategoriaASCDESC($campo_orden)
{
    $condicion_ASCDESC = "";

    switch ($campo_orden) {
        case 'alfabetico_asc':
            $condicion_ASCDESC = 'ORDER BY cp.nombre_categoria ASC';
            break;
        case 'alfabetico_desc':
            $condicion_ASCDESC = 'ORDER BY cp.nombre_categoria DESC';
            break;
        case 'cantidad_asc':
            $condicion_ASCDESC = 'ORDER BY cantidad_productos ASC';
            break;
        case 'cantidad_desc':
            $condicion_ASCDESC = 'ORDER BY cantidad_productos DESC';
            break;
        default:
            $condicion_ASCDESC = 'ORDER BY cp.nombre_categoria ASC';
    }
    return  $condicion_ASCDESC;
}