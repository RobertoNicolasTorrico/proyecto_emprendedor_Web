<?php

function altaSeguimientoUsuario($conexion, $id_usuario_emprendedor_seguido, $id_usuario_seguidor)
{
    try {
        $fecha = date("Y-m-d H:i:s");
        $sentenciaSQL = $conexion->prepare("INSERT INTO seguimiento(fecha_seguimiento,id_usuario_emprendedor_seguido, id_usuario_seguidor) 
                                            VALUES (?,?,?)");
        $sentenciaSQL->bindParam(1, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $id_usuario_emprendedor_seguido, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $id_usuario_seguidor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta altaSeguimientoUsuario:" . $e->getMessage());
    }
}

function bajaSeguimientoUsuario($conexion, $id_usuario_emprendedor_seguido, $id_usuario_seguidor)
{
    try {
        $sentenciaSQL = $conexion->prepare("DELETE FROM seguimiento WHERE id_usuario_emprendedor_seguido = ? AND id_usuario_seguidor=?");
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor_seguido, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_seguidor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaSeguimientoUsuario:" . $e->getMessage());
    }
}

function verificarSiElUsuarioSigueAlEmprendedor($conexion, $id_usuario_emprendedor_seguido, $id_usuario_seguidor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM seguimiento
                                            WHERE id_usuario_emprendedor_seguido = ? AND id_usuario_seguidor = ?
                                            LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor_seguido, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_seguidor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiElUsuarioSigueAlEmprendedor:" . $e->getMessage());
    }
}



function obtenerProductosDeLosEmprendedoresQueSiSegue($conexion, $id_usuario, $condicion_limit)
{
    try {
        $id_disponible = 1;
        $activado = 1;
        $baneado = 0;

        $sentenciaSQL = $conexion->prepare("SELECT pp.*,cp.nombre_categoria,up.nombre_emprendimiento,up.foto_perfil_nombre
                                            FROM publicacion_producto pp 
                                            INNER JOIN usuario_emprendedor up ON pp.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario
                                            INNER JOIN categoria_producto cp ON  pp.id_categoria_producto = cp.id_categoria_producto
                                            WHERE pp.id_usuario_emprendedor IN(SELECT s.id_usuario_emprendedor_seguido 
                                                                                FROM seguimiento s 
                                                                                WHERE s.id_usuario_seguidor = ? ) AND id_estado_producto =? AND u.activado = ? AND u.baneado=?
                                            ORDER BY pp.fecha_publicacion DESC " . $condicion_limit);
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_disponible, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $baneado, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $productos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $productos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerProductosDeLosEmprendedoresQueSiSegue:" . $e->getMessage());
    }
}

function obtenerPuInformacionDeLosEmprendedoresQueSiSegue($conexion, $id_usuario, $condicion_limit)
{
    try {
        $activado = 1;
        $baneado = 0;

        $sentenciaSQL = $conexion->prepare("SELECT p.*,up.nombre_emprendimiento,up.foto_perfil_nombre
                                            FROM publicacion_informacion p
                                            INNER JOIN usuario_emprendedor up ON p.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario
                                            WHERE p.id_usuario_emprendedor IN(SELECT s.id_usuario_emprendedor_seguido 
                                                                                FROM seguimiento s 
                                                                                WHERE s.id_usuario_seguidor = ? ) AND u.activado = ? AND u.baneado=?
                                            ORDER BY p.fecha_publicacion DESC " . $condicion_limit);
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $baneado, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $productos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $productos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerPuInformacionDeLosEmprendedoresQueSiSegue:" . $e->getMessage());
    }
}

function cantTotalSeguimientoUsuario($conexion, $id_usuario)
{
    try {
        $activado = 1;
        $baneado = 0;

        $sentenciaSQL = $conexion->prepare("SELECT COUNT(*) as cant_seguidos 
                                            FROM seguimiento s
                                            INNER JOIN usuario_emprendedor up ON s.id_usuario_emprendedor_seguido = up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario
                                            WHERE s.id_usuario_seguidor =? AND u.activado = ? AND u.baneado=?");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $baneado, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cant_seguidos'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalSeguimientoUsuario:" . $e->getMessage());
    }
}

function obtenerListaEmprendedoresSeguidos($conexion, $id_usuario_seguidor, $condicion_limit, $condicion_where)
{
    try {
        $activado = 1;
        $baneado = 0;

        $sentenciaSQL = $conexion->prepare("SELECT up.id_usuario,u.nombre_usuario,up.nombre_emprendimiento,up.foto_perfil_nombre,up.id_usuario_emprendedor,s.fecha_seguimiento,s.id_usuario_seguidor
        FROM seguimiento s 
        INNER JOIN usuario_emprendedor up ON s.id_usuario_emprendedor_seguido =up.id_usuario_emprendedor 
        INNER JOIN usuario u ON up.id_usuario =u.id_usuario 
        WHERE s.id_usuario_seguidor =? AND u.activado=? AND u.baneado=? " . $condicion_where . " ORDER BY s.fecha_seguimiento DESC " . $condicion_limit);
        $sentenciaSQL->bindParam(1, $id_usuario_seguidor, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $baneado, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $lista_usuarios_emprendedores = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $lista_usuarios_emprendedores;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaEmprendedoresSeguidos:" . $e->getMessage());
    }
}

function cantTotalSeguidosWheUsuarioEmprendedor($conexion, $id_usuario, $condicion_where)
{
    try {
        $activado = 1;
        $baneado = 0;

        $sentenciaSQL = $conexion->prepare("SELECT COUNT(*) as cantidad_total 
                                            FROM usuario_emprendedor up
                                            INNER JOIN seguimiento s ON  s.id_usuario_emprendedor_seguido = up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON up.id_usuario =u.id_usuario 
                                            WHERE s.id_usuario_seguidor =? AND u.activado=? AND u.baneado=? " . $condicion_where);
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $baneado, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalSeguidosWheUsuarioEmprendedor:" . $e->getMessage());
    }
}

function obtenerListasSeguidoresDeEmprendedores($conexion, $id_usuario, $condicion_limit, $condicion_where)
{
    try {
        $activado = 1;
        $baneado = 0;

        $sentenciaSQL = $conexion->prepare("SELECT s.id_seguimiento,u.id_usuario, CONCAT(u.nombres,' ', u.apellidos) AS nombre_completo, u.nombre_usuario ,s.fecha_seguimiento,s.id_usuario_seguidor
                                            FROM seguimiento s 
                                            INNER JOIN usuario_emprendedor up ON s.id_usuario_emprendedor_seguido = up.id_usuario_emprendedor 
                                            INNER JOIN usuario u ON s.id_usuario_seguidor = u.id_usuario 
                                            WHERE up.id_usuario =? AND u.activado=? AND u.baneado=? " . $condicion_where . " ORDER BY s.fecha_seguimiento DESC " . $condicion_limit);
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $baneado, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $lista_usuarios = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $lista_usuarios;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListasSeguidoresDeEmprendedores:" . $e->getMessage());
    }
}


function cantTotalSeguidoresWheUsuarioEmprendedor($conexion, $id_usuario, $condicion_where)
{
    try {
        $activado = 1;
        $baneado = 0;

        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM seguimiento s 
                                            INNER JOIN usuario_emprendedor up ON s.id_usuario_emprendedor_seguido = up.id_usuario_emprendedor 
                                            INNER JOIN usuario u ON s.id_usuario_seguidor = u.id_usuario 
                                            WHERE up.id_usuario =? AND u.activado=? AND u.baneado=? " . $condicion_where);
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $baneado, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalSeguidoresWheUsuarioEmprendedor:" . $e->getMessage());
    }
}

function cantTotalSeguidoresUsuarioEmprendedor($conexion, $id_usuario)
{
    try {
        $activado = 1;
        $baneado = 0;

        $sentenciaSQL = $conexion->prepare("SELECT COUNT(*) as cant_seguidores
                                            FROM seguimiento s
                                            INNER JOIN usuario_emprendedor up ON s.id_usuario_emprendedor_seguido =up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON s.id_usuario_seguidor = u.id_usuario
                                            WHERE up.id_usuario = ? AND u.activado = ? AND u.baneado = ?");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $baneado, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cant_seguidores'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalSeguidoresUsuarioEmprendedor:" . $e->getMessage());
    }
}
