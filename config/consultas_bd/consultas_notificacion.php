<?php

function altaNotificacionSeguirUsuario($conexion, $id_usuario_notificar, $id_usuario_interaccion)
{
    try {

        $fecha = date("Y-m-d H:i:s");
        $id_tipo_notificacion = 1;
        $sentenciaSQL = $conexion->prepare("INSERT INTO notificaciones(id_tipo_notificacion,fecha_notificacion,id_usuario_notificar,id_usuario_interaccion) 
                                            VALUES (?,?,?,?)");
        $sentenciaSQL->bindParam(1, $id_tipo_notificacion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_usuario_notificar, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $id_usuario_interaccion, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta altaNotificacionSeguirUsuario: " . $e->getMessage());
    }
}

function bajaNotificacionSeguirUsuario($conexion, $id_usuario_notificar, $id_usuario_interaccion)
{
    try {
        $id_tipo_notificacion = 1;

        $sentenciaSQL = $conexion->prepare("DELETE 
                                            FROM notificaciones
                                            WHERE id_usuario_notificar=? AND id_usuario_interaccion=? AND id_tipo_notificacion=?");
        $sentenciaSQL->bindParam(1, $id_usuario_notificar, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_interaccion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $id_tipo_notificacion, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaNotificacionSeguirUsuario: " . $e->getMessage());
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function altaNotificacionPreguntaProducto($conexion, $id_usuario_notificar, $id_usuario_interaccion, $id_pegunta, $id_producto)
{

    try {

        $id_tipo_notificacion = 2;

        $sentenciaSQL = $conexion->prepare("INSERT INTO notificaciones(id_tipo_notificacion,id_usuario_notificar,id_usuario_interaccion,id_pregunta,id_producto) 
                                            VALUES (?,?,?,?,?)");
        $sentenciaSQL->bindParam(1, $id_tipo_notificacion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_notificar, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $id_usuario_interaccion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $id_pegunta, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(5, $id_producto, PDO::PARAM_INT);

        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta altaNotificacionPreguntaProducto: " . $e->getMessage());
    }
}

function bajaNotificacionPreguntaProducto($conexion, $id_usuario_notificar, $id_usuario_interaccion, $id_pregunta)
{
    try {

        $id_tipo_notificacion = 2;

        $sentenciaSQL = $conexion->prepare("DELETE 
                                            FROM notificaciones
                                            WHERE id_usuario_notificar=? AND id_usuario_interaccion=? AND id_tipo_notificacion=? AND id_pregunta=?");
        $sentenciaSQL->bindParam(1, $id_usuario_notificar, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_interaccion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $id_tipo_notificacion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $id_pregunta, PDO::PARAM_INT);

        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaNotificacionPreguntaProducto: " . $e->getMessage());
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function altaNotificacionRespuestaPregunta($conexion, $id_usuario_notificar, $id_usuario_interaccion, $id_respuesta, $id_producto)
{
    try {

        $id_tipo_notificacion = 3;

        $sentenciaSQL = $conexion->prepare("INSERT INTO notificaciones(id_tipo_notificacion,id_usuario_notificar,id_usuario_interaccion,id_respuesta ,id_producto) 
                                            VALUES (?,?,?,?,?)");
        $sentenciaSQL->bindParam(1, $id_tipo_notificacion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_notificar, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $id_usuario_interaccion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $id_respuesta, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(5, $id_producto, PDO::PARAM_INT);

        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta altaNotificacionRespuestaPregunta: " . $e->getMessage());
    }
}

function bajaNotificacionRespuestaPregunta($conexion, $id_usuario_notificar, $id_usuario_interaccion, $id_respuesta)
{
    try {
        $id_tipo_notificacion = 3;
        $sentenciaSQL = $conexion->prepare("DELETE 
                                            FROM notificaciones
                                            WHERE id_usuario_notificar=? AND id_usuario_interaccion=? AND id_tipo_notificacion=? AND id_respuesta=?");
        $sentenciaSQL->bindParam(1, $id_usuario_notificar, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_interaccion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $id_tipo_notificacion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $id_respuesta, PDO::PARAM_INT);

        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaNotificacionRespuestaPregunta: " . $e->getMessage());
    }
}

function bajaNotificacion($conexion, $id_notificacion, $id_usuario_notificar)
{
    try {
        $sentenciaSQL = $conexion->prepare("DELETE 
                                            FROM notificaciones
                                            WHERE id_notificacion = ? AND id_usuario_notificar=?");
        $sentenciaSQL->bindParam(1, $id_notificacion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_notificar, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaNotificacion: " . $e->getMessage());
    }
}


function bajaNotificacionProducto($conexion, $id_producto)
{
    try {
        $sentenciaSQL = $conexion->prepare("DELETE 
                                            FROM notificaciones
                                            WHERE id_producto = ?");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaNotificacionProducto: " . $e->getMessage());
    }
}


function obtenerListaUltimasNotificaciones($conexion, $id_usuario_notificar)
{
    try {
        $sentencia = $conexion->prepare("SELECT n.*,u.nombre_usuario,pr.pregunta,pr.fecha_pregunta,rr.respuesta,rr.fecha_respuesta,pp.nombre_producto,tn.tipo as tipo_notificacion
                                        FROM notificaciones n 
                                        INNER JOIN usuario u ON n.id_usuario_interaccion = u.id_usuario 
                                        INNER JOIN tipo_notificacion tn ON n.id_tipo_notificacion=tn.id_tipo_notificacion  
                                        LEFT JOIN pregunta_respuesta pr ON n.id_pregunta = pr.id_pregunta_respuesta 
                                        LEFT JOIN pregunta_respuesta rr ON n.id_respuesta = rr.id_pregunta_respuesta 
                                        LEFT JOIN publicacion_producto pp ON n.id_producto = pp.id_publicacion_producto 
                                        WHERE n.id_usuario_notificar = ? 
                                        ORDER BY fecha_notificacion DESC LIMIT 0,5");
        $sentencia->bindParam(1, $id_usuario_notificar, PDO::PARAM_INT);
        $sentencia->execute();
        $lista_notificacion = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $lista_notificacion;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaUltimasNotificaciones: " . $e->getMessage());
    }
}

function obtenerListaDeTodasMisNotificacionesWWhereLimit($conexion, $id_usuario_notificar, $condicion_where, $condicion_limit)
{
    try {
        $sentencia = $conexion->prepare("SELECT n.*,u.nombre_usuario,pr.pregunta,pr.fecha_pregunta,rr.respuesta,rr.fecha_respuesta,pp.nombre_producto,tn.tipo as tipo_notificacion
                                        FROM notificaciones n 
                                        INNER JOIN usuario u ON n.id_usuario_interaccion = u.id_usuario 
                                        INNER JOIN tipo_notificacion tn ON n.id_tipo_notificacion=tn.id_tipo_notificacion  
                                        LEFT JOIN pregunta_respuesta pr ON n.id_pregunta = pr.id_pregunta_respuesta 
                                        LEFT JOIN pregunta_respuesta rr ON n.id_respuesta = rr.id_pregunta_respuesta 
                                        LEFT JOIN publicacion_producto pp ON n.id_producto = pp.id_publicacion_producto 
                                        WHERE n.id_usuario_notificar = ? " . $condicion_where . "
                                        ORDER BY fecha_notificacion DESC " . $condicion_limit);
        $sentencia->bindParam(1, $id_usuario_notificar, PDO::PARAM_INT);
        $sentencia->execute();
        $lista_notificacion = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $lista_notificacion;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaDeTodasMisNotificacionesWWhereLimit: " . $e->getMessage());
    }
}

function obtenerCantidadNotificacionSinLeer($conexion, $id_usuario_notificar)
{
    try {
        $leido = 0;
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad
                                        FROM  notificaciones
                                        WHERE id_usuario_notificar=? AND leido = ?");
        $sentenciaSQL->bindParam(1, $id_usuario_notificar, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $leido, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerCantidadNotificacionSinLeer: " . $e->getMessage());
    }
}

function modificarNotificacionALeida($conexion, $id_notificacion)
{
    try {
        $leida = 1;
        $sentenciaSQL = $conexion->prepare("UPDATE notificaciones 
                                            SET leido=?
                                            WHERE id_notificacion =?");

        $sentenciaSQL->bindParam(1, $leida, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_notificacion, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarNotificacionALeida: " . $e->getMessage());
    }
}

function modificarNotificacionALeidaNavbar($conexion, $id_notificaciones)
{
    try {
        $leida = 1;
        $sentenciaSQL = $conexion->prepare("UPDATE notificaciones 
                                            SET leido=?
                                            WHERE id_notificacion IN (" . $id_notificaciones . ")");

        $sentenciaSQL->bindParam(1, $leida, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarNotificacionALeidaNavbar: " . $e->getMessage());
    }
}
