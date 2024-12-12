<?php


function altaPreguntaProducto($conexion, $pregunta, $id_usuario, $id_producto)
{
    try {
        $fecha = date("Y-m-d H:i:s");
        $sentenciaSQL = $conexion->prepare("INSERT INTO pregunta_respuesta(pregunta,fecha_pregunta ,id_usuario,id_producto)  
                                    VALUES (?,?,?,?);");

        $sentenciaSQL->bindParam(1, $pregunta, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $id_producto, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $id_pegunta = $conexion->lastInsertid();
        return  $id_pegunta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta altaPreguntaProducto: " . $e->getMessage());
    }
}


function bajaPreguntaProducto($conexion, $id_pregunta)
{
    try {
        $sentenciaSQL = $conexion->prepare("DELETE FROM pregunta_respuesta WHERE id_pregunta_respuesta = ?");
        $sentenciaSQL->bindParam(1, $id_pregunta, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaPreguntaProducto: " . $e->getMessage());
    }
}

function bajaTodasPreguntasDelProducto($conexion, $id_producto)
{
    try {
        $sentenciaSQL = $conexion->prepare("DELETE FROM pregunta_respuesta WHERE id_producto = ?");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaTodasPreguntasDelProducto: " . $e->getMessage());
    }
}


function altaRespuestaModificarPreguntaProducto($conexion, $respuesta, $id_pregunta)
{
    try {
        $fecha = date("Y-m-d H:i:s");
        $sentenciaSQL = $conexion->prepare("UPDATE pregunta_respuesta 
                                            SET respuesta=?,fecha_respuesta=? 
                                            WHERE id_pregunta_respuesta =?");

        $sentenciaSQL->bindParam(1, $respuesta, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_pregunta, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta altaRespuestaModificarPreguntaProducto: " . $e->getMessage());
    }
}

function bajaRespuestaModificarPreguntaProducto($conexion, $id_pregunta)
{
    try {
        $fecha = null;
        $respuesta = null;
        $sentenciaSQL = $conexion->prepare("UPDATE pregunta_respuesta 
                                            SET respuesta=?,fecha_respuesta=? 
                                            WHERE id_pregunta_respuesta =?");

        $sentenciaSQL->bindParam(1, $respuesta, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_pregunta, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaRespuestaModificarPreguntaProducto: " . $e->getMessage());
    }
}


function obtenerListaPreguntasRespuestaProducto($conexion, $id_producto)
{
    try {
        $sentencia = $conexion->prepare("SELECT pr.*,u.nombre_usuario  
                 FROM  pregunta_respuesta pr 
                INNER JOIN usuario u ON pr.id_usuario=u.id_usuario
                WHERE id_producto=? 
                ORDER BY fecha_pregunta DESC,id_producto ASC ;");
        $sentencia->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentencia->execute();
        $lista_preguntas_respuesta = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $lista_preguntas_respuesta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaPreguntasRespuestaProducto: " . $e->getMessage());
    }
}


function obtenerIdUsuarioPreguntaProducto($conexion, $id_pregunta)
{
    try {
        $sentencia = $conexion->prepare("SELECT ep.id_usuario
                                        FROM pregunta_respuesta pr
                                        INNER JOIN publicacion_producto pp ON pr.id_producto = pp.id_publicacion_producto 
                                        INNER JOIN usuario_emprendedor ep ON pp.id_usuario_emprendedor = ep.id_usuario_emprendedor
                                        WHERE pr.id_pregunta_respuesta=?  LIMIT 1;");
        $sentencia->bindParam(1, $id_pregunta, PDO::PARAM_INT);
        $sentencia->execute();
        $lista_preguntas_respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
        return $lista_preguntas_respuesta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerIdUsuarioPreguntaProducto: " . $e->getMessage());
    }
}

function obtenerIdUsuarioPreguntaRespuesta($conexion, $id_pregunta)
{
    try {
        $sentencia = $conexion->prepare("SELECT pr.id_usuario
                                        FROM pregunta_respuesta pr
                                        WHERE pr.id_pregunta_respuesta=? LIMIT 1;");
        $sentencia->bindParam(1, $id_pregunta, PDO::PARAM_INT);
        $sentencia->execute();
        $lista_preguntas_respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
        return $lista_preguntas_respuesta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerIdUsuarioPreguntaRespuesta: " . $e->getMessage());
    }
}

function obtenerPreguntaHecha($conexion, $id_pregunta)
{
    try {
        $sentencia = $conexion->prepare("SELECT *
                                        FROM pregunta_respuesta pr
                                        WHERE pr.id_pregunta_respuesta=? LIMIT 1;");
        $sentencia->bindParam(1, $id_pregunta, PDO::PARAM_INT);
        $sentencia->execute();
        $lista_preguntas_respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
        return $lista_preguntas_respuesta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerPreguntaHecha: " . $e->getMessage());
    }
}

function obtenerListaPreguntasRespuestaWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT pp.nombre_producto,pp.id_usuario_emprendedor, u.nombre_usuario, pr.* ,ep.estado, app.nombre_carpeta,MIN(app.nombre_archivo) AS nombre_archivo,pr.*
                                            FROM publicacion_producto pp
                                            INNER JOIN estado_producto ep ON pp.id_estado_producto = ep.id_estado_producto 
                                            INNER JOIN archivo_publicacion_producto app ON app.id_publicacion_producto =pp.id_publicacion_producto 
                                            INNER JOIN pregunta_respuesta pr ON pp.id_publicacion_producto = pr.id_producto 
                                            INNER JOIN usuario u ON pr.id_usuario = u.id_usuario 
                                            WHERE pp.id_usuario_emprendedor=? " . $condicion_where . "
                                            GROUP BY pr.id_pregunta_respuesta
                                            ORDER BY pr.fecha_pregunta DESC " . $condicion_limit);

        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $listaProductos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaProductos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaPreguntasRespuestaWhereLimit: " . $e->getMessage());
    }
}

function cantTotalListaPreguntasRespuestaWhere($conexion, $condicion_where, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM  estado_producto ep
                                            INNER JOIN publicacion_producto pp ON pp.id_estado_producto = ep.id_estado_producto
                                            INNER JOIN pregunta_respuesta pr ON pp.id_publicacion_producto = pr.id_producto
                                               INNER JOIN usuario u ON pr.id_usuario = u.id_usuario 
                                            WHERE pp.id_usuario_emprendedor=? " . $condicion_where);
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalListaPreguntasRespuestaWhere: " . $e->getMessage());
    }
}


function obtenerListaPreguntasRespuestaProductoUsuario($conexion, $id_producto, $id_usuario)
{
    try {
        $sentencia = $conexion->prepare("SELECT *
                                        FROM  pregunta_respuesta 
                                        WHERE id_producto=? AND id_usuario=?
                                        ORDER BY fecha_pregunta DESC;");
        $sentencia->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentencia->bindParam(2, $id_usuario, PDO::PARAM_INT);
        $sentencia->execute();
        $lista_preguntas_respuesta = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $lista_preguntas_respuesta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaPreguntasRespuestaProductoUsuario: " . $e->getMessage());
    }
}


function obtenerListaPreguntasRespuestaProductoSinElUsuario($conexion, $id_producto, $id_usuario)
{
    try {
        $sentencia = $conexion->prepare("SELECT * 
                                        FROM  pregunta_respuesta 
                                        WHERE id_producto=? AND id_usuario !=?
                                        ORDER BY fecha_pregunta DESC;");
        $sentencia->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentencia->bindParam(2, $id_usuario, PDO::PARAM_INT);
        $sentencia->execute();
        $lista_preguntas_respuesta = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $lista_preguntas_respuesta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaPreguntasRespuestaProductoSinElUsuario: " . $e->getMessage());
    }
}


function obtenerListaMisPreguntasWhereLimit($conexion, $condicion_where, $condicion_limit, $id_usuario)
{
    try {
        $sentencia = $conexion->prepare("SELECT pp.id_usuario_emprendedor,pp.nombre_producto,pp.precio,pp.stock,ep.estado,app.nombre_carpeta,MIN(app.nombre_archivo) AS nombre_archivo,pr.* 
                                        FROM publicacion_producto pp 
                                        INNER JOIN pregunta_respuesta pr ON pp.id_publicacion_producto = pr.id_producto 
                                        INNER JOIN (SELECT pr.id_producto, MAX(pr.fecha_pregunta) AS ultima_pregunta 
                                                    FROM pregunta_respuesta pr 
                                                    GROUP BY pr.id_producto ) ultima_pregunta_por_producto  ON pr.id_producto = ultima_pregunta_por_producto.id_producto 
                                        INNER JOIN estado_producto ep ON pp.id_estado_producto = ep.id_estado_producto 
                                        INNER JOIN archivo_publicacion_producto app ON pp.id_publicacion_producto = app.id_publicacion_producto 
                                        WHERE pr.id_usuario=? " . $condicion_where . "
                                        GROUP BY pr.id_pregunta_respuesta 
                                        ORDER BY ultima_pregunta_por_producto.ultima_pregunta DESC,pr.fecha_pregunta DESC " . $condicion_limit);
        $sentencia->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentencia->execute();
        $lista_preguntas_respuesta = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $lista_preguntas_respuesta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaMisPreguntasWhereLimit: " . $e->getMessage());
    }
}


function modificarPreguntaPorAdmin($conexion, $id_pregunta, $pregunta, $fecha_pregunta)
{
    try {

        $sentenciaSQL = $conexion->prepare("UPDATE pregunta_respuesta 
                                            SET pregunta=?,fecha_pregunta=?
                                            WHERE id_pregunta_respuesta =?");

        $sentenciaSQL->bindParam(1, $pregunta, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $fecha_pregunta, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_pregunta, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarPreguntaPorAdmin: " . $e->getMessage());
    }
}


function modificarRespuestaPorAdmin($conexion, $id_respuesta, $respuesta, $fecha_respuesta)
{
    try {

        $sentenciaSQL = $conexion->prepare("UPDATE pregunta_respuesta 
                                            SET respuesta=?,fecha_respuesta=?
                                            WHERE id_pregunta_respuesta =?");
        $sentenciaSQL->bindParam(1, $respuesta, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $fecha_respuesta, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_respuesta, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarRespuestaPorAdmin: " . $e->getMessage());
    }
}


function cantTotalMisPreguntasProductoWhe($conexion, $condicion_where, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM publicacion_producto pp 
                                            INNER JOIN pregunta_respuesta pr ON pp.id_publicacion_producto = pr.id_producto 
                                            WHERE pr.id_usuario=? " . $condicion_where . "");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalMisPreguntasProductoWhe: " . $e->getMessage());
    }
}


function elUsuarioHizoLaPregunta($conexion, $id_pregunta, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM  pregunta_respuesta
                                            WHERE id_pregunta_respuesta = ? AND id_usuario = ? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_pregunta, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta elUsuarioHizoLaPregunta: " . $e->getMessage());
    }
}

function laPreguntaFueRespondida($conexion, $id_pregunta)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM  pregunta_respuesta
                                            WHERE id_pregunta_respuesta = ? AND respuesta IS NOT NULL AND fecha_respuesta IS NOT NULL LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_pregunta, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta laPreguntaFueRespondida: " . $e->getMessage());
    }
}

function laPreguntaSigueDisponible($conexion, $id_pregunta)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM  pregunta_respuesta
                                            WHERE id_pregunta_respuesta = ? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_pregunta, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta laPreguntaSigueDisponible: " . $e->getMessage());
    }
}


