<?php


function obtenerCondicionWhereNotificacion($estado, $cant_dias)
{
    $condicion_where = "";
    $condicion_where .= obtenerCondicionWhereCantDias('n.fecha_notificacion', $cant_dias);
    if ($estado != 'todos') {
        $condicion_where .= obtenerCondicionWhereIgual('n.leido', $estado);
    }
    return $condicion_where;
}



?>