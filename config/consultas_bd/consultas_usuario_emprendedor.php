<?php

function obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT up.*, (SELECT COUNT(*) 
                                                        FROM seguimiento s1 
                                                        WHERE s1.id_usuario_emprendedor_seguido = up.id_usuario_emprendedor) as cant_seguidores, 
                                                    (SELECT COUNT(*) 
                                                    FROM seguimiento s2 
                                                    WHERE s2.id_usuario_seguidor = u.id_usuario) as cant_seguidor, u.nombre_usuario 
                                            FROM usuario_emprendedor up 
                                            INNER JOIN usuario u ON u.id_usuario = up.id_usuario 
                                            WHERE  u.id_usuario =? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerDatosUsuarioEmprendedorPorIDUsuario: " . $e->getMessage());
    }
}
function obtenerDatosUsuarioEmprendedorPorIDUsuarioEmprendedor($conexion, $id_usuario_emprendedor)
{
    try {
        $estado_disponible = 1;

        $sentenciaSQL = $conexion->prepare("SELECT u.id_usuario,u.baneado, u.activado, u.nombre_usuario,up.*, 
                                                            (SELECT COUNT(*) 
                                                                FROM seguimiento s1 
                                                                WHERE s1.id_usuario_emprendedor_seguido = up.id_usuario_emprendedor) as cant_seguidores, 
                                                            (SELECT COUNT(*) 
                                                                FROM seguimiento s2 
                                                                WHERE s2.id_usuario_seguidor = u.id_usuario) as cant_seguidor,
                                                            (SELECT COUNT(*)
                                                                FROM publicacion_producto pp
                                                                WHERE pp.id_usuario_emprendedor=? AND pp.id_estado_producto=?) as productos_disponibles
                                            FROM usuario_emprendedor up 
                                            INNER JOIN usuario u ON u.id_usuario = up.id_usuario 
                                            WHERE up.id_usuario_emprendedor = ? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);

        $sentenciaSQL->bindParam(2, $estado_disponible, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $id_usuario_emprendedor, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerDatosUsuarioEmprendedorPorIDUsuarioEmprendedor: " . $e->getMessage());
    }
}

function obtenerDatosUsuarioYUsuarioEmprendedorPorIdUsuario($conexion, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario_emprendedor up 
                                            INNER JOIN usuario u ON u.id_usuario=up.id_usuario
                                            WHERE up.id_usuario =? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerDatosUsuarioYUsuarioEmprendedorPorIdUsuario: " . $e->getMessage());
    }
}

function esPerfilDelUsuario($conexion, $id_usuario, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM  usuario_emprendedor 
                                            WHERE id_usuario = ? AND id_usuario_emprendedor = ?
                                            LIMIT 1;");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta esPerfilDelUsuario: " . $e->getMessage());
    }
}

function obtenerListaEmprendedoresActivosParaIndex($conexion, $limit)
{
    try {
        $activado = 1;
        $baneado = 0;
        $disponible = 1;

        $sentenciaSQL = $conexion->prepare("SELECT u.nombre_usuario,up.* , COUNT(pp.id_publicacion_producto) AS cant_productos_publicados
                                             FROM usuario_emprendedor up 
                                             LEFT JOIN publicacion_producto pp ON up.id_usuario_emprendedor = pp.id_usuario_emprendedor 
                                             INNER JOIN usuario u ON up.id_usuario = u.id_usuario 
                                            WHERE u.activado =? AND u.baneado=? AND (pp.id_estado_producto = ? OR pp.id_estado_producto IS NULL)  GROUP BY up.id_usuario_emprendedor  ORDER BY RAND()" . $limit);
        $sentenciaSQL->bindParam(1, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $baneado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $disponible, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $listaEmprendedores = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaEmprendedores;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaEmprendedoresActivosParaIndex: " . $e->getMessage());
    }
}

function obtenerListaBusquedaEmprendedoresWhereASCDESCLimit($conexion, $where, $ASC_DESC, $limit)
{
    try {
        $activado = 1;
        $baneado = 0;
        $sentenciaSQL = $conexion->prepare("SELECT u.nombre_usuario,up.* , SUM(CASE WHEN pp.id_estado_producto != 2 THEN 1 ELSE 0 END) AS cant_productos_publicados
                                            FROM usuario_emprendedor up 
                                            LEFT JOIN publicacion_producto pp ON up.id_usuario_emprendedor = pp.id_usuario_emprendedor 
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario 
                                            WHERE u.activado =? AND u.baneado=?  " . $where . " GROUP BY up.id_usuario_emprendedor  ORDER BY " . $ASC_DESC . " " . $limit);

        $sentenciaSQL->bindParam(1, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $baneado, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $listaEmprendedores = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaEmprendedores;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaBusquedaEmprendedoresWhereASCDESCLimit: " . $e->getMessage());
    }
}

function cantTotalListaBusquedaEmprendedoresWhere($conexion, $where)
{
    try {
        $activado = 1;
        $baneado = 0;
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                           FROM usuario u 
                                            INNER JOIN usuario_emprendedor up ON  u.id_usuario =up.id_usuario
                                            WHERE u.activado =? AND u.baneado=? " . $where);
        $sentenciaSQL->bindParam(1, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $baneado, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalListaBusquedaEmprendedoresWhere: " . $e->getMessage());
    }
}

function modificarDatosEmprendedor($conexion, $id_usuario_emprendedor, $descripcion, $file, $foto_perfil_nombre)
{
    try {
        $fecha_modificacion = date("Y-m-d H:i:s");
        global $url_base_guardar_archivos;
        $conexion->beginTransaction();
        if (empty($descripcion)) {
            $descripcion = NULL;
        }
        $sentenciaSQL = $conexion->prepare("UPDATE usuario_emprendedor 
                                            SET descripcion=?
                                            WHERE id_usuario_emprendedor = ?");
        $sentenciaSQL->bindParam(1, $descripcion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();

        if (!empty($file)) {
            $ruta = $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor . "/foto_perfil";
            if (!is_null($foto_perfil_nombre)) {
                $ruta_archivo = $ruta . "/" . $foto_perfil_nombre;

                if (is_file($ruta_archivo)) {
                    unlink($ruta_archivo);
                } else {
                    throw new Exception("No se pudo eliminar el archivo actual de la foto de perfil");
                }
            }
            crearNuevaCarpeta($ruta);
            $extencion_archivo = pathinfo($file['name'], PATHINFO_EXTENSION);
            $nombre_archivo = str_replace([" ", ":"], ["_", "-"], $fecha_modificacion) . "." . $extencion_archivo;
            if (move_uploaded_file($file['tmp_name'], $ruta . "/" . $nombre_archivo)) {
                $sentenciaSQL = $conexion->prepare("UPDATE usuario_emprendedor 
                                                        SET foto_perfil_nombre=? 
                                                        WHERE id_usuario_emprendedor = ?");

                $sentenciaSQL->bindParam(1, $nombre_archivo, PDO::PARAM_STR);
                $sentenciaSQL->bindParam(2, $id_usuario_emprendedor, PDO::PARAM_INT);
                $sentenciaSQL->execute();
            } else {
                eliminarCarpetaConArchivos($ruta);
                throw new Exception("No se pudo guardar el archivo de la nueva foto del perfil");
            }
        }
        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta modificarDatosBasicosEmprendedor: " . $e->getMessage());
    }
}

function modificarDatosEmprendedorPorAdministrador($conexion, $id_usuario_emprendedor, $nombre_emprendimiento, $descripcion, $file, $calificacion_emprendedor, $foto_perfil_nombre)
{
    try {
        $fecha_modificacion = date("Y-m-d H:i:s");
        global $url_base_guardar_archivos;
        $conexion->beginTransaction();
        if (empty($descripcion)) {
            $descripcion = NULL;
        }
        $sentenciaSQL = $conexion->prepare("UPDATE usuario_emprendedor 
                                            SET nombre_emprendimiento=?, descripcion=?,calificacion_emprendedor=?
                                            WHERE id_usuario_emprendedor = ?");
        $sentenciaSQL->bindParam(1, $nombre_emprendimiento, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $descripcion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $calificacion_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();

        if (!empty($file)) {
            $ruta = $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor . "/foto_perfil";
            if (!is_null($foto_perfil_nombre)) {
                $ruta_archivo = $ruta . "/" . $foto_perfil_nombre;

                if (is_file($ruta_archivo)) {
                    unlink($ruta_archivo);
                } else {
                    throw new Exception("No se pudo eliminar el archivo actual de la foto de perfil");
                }
            }
            crearNuevaCarpeta($ruta);
            $extencion_archivo = pathinfo($file['name'], PATHINFO_EXTENSION);
            $nombre_archivo = str_replace([" ", ":"], ["_", "-"], $fecha_modificacion) . "." . $extencion_archivo;
            if (move_uploaded_file($file['tmp_name'], $ruta . "/" . $nombre_archivo)) {
                $sentenciaSQL = $conexion->prepare("UPDATE usuario_emprendedor 
                                                        SET foto_perfil_nombre=? 
                                                        WHERE id_usuario_emprendedor = ?");

                $sentenciaSQL->bindParam(1, $nombre_archivo, PDO::PARAM_STR);
                $sentenciaSQL->bindParam(2, $id_usuario_emprendedor, PDO::PARAM_INT);
                $sentenciaSQL->execute();
            } else {
                eliminarCarpetaConArchivos($ruta);
                throw new Exception("No se pudo guardar el archivo de la nueva foto del perfil");
            }
        }
        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta modificarDatosEmprendedor: " . $e->getMessage());
    }
}
