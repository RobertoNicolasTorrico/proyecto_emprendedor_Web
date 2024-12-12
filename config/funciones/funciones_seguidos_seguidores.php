<?php

function obtenerCondicionWhereBuscarSeguidorAdmin($campo_buscar,$campo_fecha)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(["u.nombre_usuario","CONCAT(u.nombres,' ', u.apellidos) "], $campo_buscar);
    $condicion_where .= obtenerCondicionWhereFecha('s.fecha_seguimiento', $campo_fecha);

    return $condicion_where;
}

function obtenerCondicionWhereBuscadorEmprendedorParaAdmin($campo_buscar,$campo_fecha)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereBuscador(['u.nombre_usuario', 'up.nombre_emprendimiento'], $campo_buscar);
    $condicion_where .= obtenerCondicionWhereFecha('s.fecha_seguimiento', $campo_fecha);

    return $condicion_where;
}