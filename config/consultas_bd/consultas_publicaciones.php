<?php

function altaPublicacionInformacion($conexion, $descripcion, $id_usuario_emprendedor, $map_longitud, $map_latitud, $archivos)
{
    try {
        $fecha = date("Y-m-d H:i:s");
        $conexion->beginTransaction();
        global $url_base_guardar_archivos;
        $sentenciaSQL = $conexion->prepare("INSERT INTO publicacion_informacion(descripcion, fecha_publicacion, map_latitud, map_longitud, id_usuario_emprendedor) 
                                            VALUES (?,?,?,?,?)");
        $sentenciaSQL->bindParam(1, $descripcion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $map_latitud, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $map_longitud, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $id_usuario_emprendedor, PDO::PARAM_INT);

        $sentenciaSQL->execute();

        if (!empty($archivos)) {
            $id_publicacion_informacion = $conexion->lastInsertid();

            $nombre_carpeta = str_replace([" ", ":"], ["_", "-"], $fecha);
            $ruta = $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor . "/publicaciones_informacion/" . $nombre_carpeta;
            crearNuevaCarpeta($ruta);
            for ($i = 0; $i < count($archivos['name']); $i++) {
                $extencion_archivo = pathinfo($archivos['name'][$i], PATHINFO_EXTENSION);
                $nombre_archivo = $nombre_carpeta . "_" . $i . "." . $extencion_archivo;
                if (move_uploaded_file($archivos['tmp_name'][$i], $ruta . "/" . $nombre_archivo)) {
                    altaArchivosPublicaciones($conexion, $nombre_carpeta, $nombre_archivo, $id_publicacion_informacion);
                } else {
                    eliminarCarpetaConArchivos($ruta);
                    throw new Exception("No se pudo guardar los archivos de la publicacion");
                }
            }
        }
        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta altaPublicacionInformacion: " . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}

function altaArchivosPublicaciones($conexion, $nombre_carpeta, $nombre_archivo, $id_publicacion_informacion)
{
    try {
        $sentenciaSQL = $conexion->prepare("INSERT INTO archivo_publicacion_informacion(nombre_carpeta,nombre_archivo, id_publicacion_informacion)
                                                    VALUES(?,?,?)");
        $sentenciaSQL->bindParam(1, $nombre_carpeta, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $nombre_archivo, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_publicacion_informacion, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta altaArchivosPublicaciones: " . $e->getMessage());
    }
}

function obtenerListaPublicacionWhereLimit($conexion, $where, $limit, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT pp.*
                                        FROM publicacion_informacion pp
                                        WHERE pp.id_usuario_emprendedor=? " . $where . "
                                        ORDER BY pp.fecha_publicacion DESC " . $limit);
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $listaProductos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaProductos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaPublicacionWhereLimit: " . $e->getMessage());
    }
}

function obtenerListaPublicacionWhereLimitAdmin($conexion, $condicion_where, $condicion_limit)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT p.*,up.nombre_emprendimiento,up.foto_perfil_nombre,up.id_usuario
                                            FROM publicacion_informacion p
                                            INNER JOIN usuario_emprendedor up ON p.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            LEFT JOIN archivo_publicacion_informacion ap ON ap.id_publicacion_informacion = p.id_publicacion_informacion " . $condicion_where . "
                                            
                                            GROUP BY p.id_publicacion_informacion  ORDER BY p.fecha_publicacion DESC " . $condicion_limit);
        $sentenciaSQL->execute();
        $listaPublicacion = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaPublicacion;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaPublicacionWhereLimitAdmin: " . $e->getMessage());
    }
}

function cantTotalTodasPublicacion($conexion, $condicion_where)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT count(DISTINCT p.id_publicacion_informacion) AS cantidad_total
                                            FROM publicacion_informacion p 
                                            INNER JOIN usuario_emprendedor up ON p.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            LEFT JOIN archivo_publicacion_informacion ap ON ap.id_publicacion_informacion = p.id_publicacion_informacion " . $condicion_where . "
                                            ");
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalTodasPublicacion: " . $e->getMessage());
    }
}

function obtenerListaArchivosPublicaciones($conexion, $id_publicaciones)
{
    try {
        $sentencia = $conexion->prepare("SELECT * 
                FROM  archivo_publicacion_informacion 
                WHERE id_publicacion_informacion=?;");
        $sentencia->bindParam(1, $id_publicaciones, PDO::PARAM_INT);
        $sentencia->execute();
        $archivos = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $archivos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaArchivosPublicaciones: " . $e->getMessage());
    }
}

function cantTotalPublicacionesWheUsuarioEmprendedor($conexion, $where, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM publicacion_informacion pp
                                            WHERE pp.id_usuario_emprendedor=? " . $where);
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalPublicacionesWheUsuarioEmprendedor: " . $e->getMessage());
    }
}

function elUsuarioHizoLaPublicacion($conexion, $id_usuario, $id_publicacion)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * FROM publicacion_informacion p
                                            INNER JOIN usuario_emprendedor up ON p.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario
                                            WHERE u.id_usuario = ? AND p.id_publicacion_informacion = ? LIMIT 1;");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_publicacion, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta elUsuarioHizoLaPublicacion: " . $e->getMessage());
    }
}

function bajaTodosLosArchivosPublicaciones($conexion, $id_publicacion)
{
    try {
        $sentenciaSQL = $conexion->prepare("DELETE FROM archivo_publicacion_informacion WHERE id_publicacion_informacion = ?");
        $sentenciaSQL->bindParam(1, $id_publicacion, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaTodosLosArchivosPublicaciones: " . $e->getMessage());
    }
}

function bajaPublicacionInformacion($conexion, $id_publicacion)
{
    try {
        $conexion->beginTransaction();
        global $url_base_guardar_archivos;
        $datos_carpeta = obtenerDatosCarpetaPublicacion($conexion, $id_publicacion);
        if (!empty($datos_carpeta)) {
            bajaTodosLosArchivosPublicaciones($conexion, $id_publicacion);
            $ruta = $url_base_guardar_archivos . "/uploads/" . $datos_carpeta['id_usuario_emprendedor'] . "/publicaciones_informacion/" . $datos_carpeta['nombre_carpeta'];
            eliminarCarpetaConArchivos($ruta);
        }
        $sentenciaSQL = $conexion->prepare("DELETE FROM publicacion_informacion WHERE id_publicacion_informacion = ?");
        $sentenciaSQL->bindParam(1, $id_publicacion, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta bajaPublicacionInformacion: " . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}

function obtenerDatosCarpetaPublicacion($conexion, $id_publicacion)
{
    try {
        $sentencia = $conexion->prepare("SELECT p.id_usuario_emprendedor , api.nombre_carpeta
                                        FROM publicacion_informacion p
                                        INNER JOIN archivo_publicacion_informacion api ON p.id_publicacion_informacion = api.id_publicacion_informacion 
                                        WHERE p.id_publicacion_informacion = ? LIMIT 1");
        $sentencia->bindParam(1, $id_publicacion, PDO::PARAM_INT);
        $sentencia->execute();
        $nombre_carpeta = $sentencia->fetch(PDO::FETCH_ASSOC);
        return $nombre_carpeta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerDatosCarpetaPublicacion: " . $e->getMessage());
    }
}

function obtenerPublicacionDelUsuarioEmprendedor($conexion, $id_publicacion, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM publicacion_informacion 
                                            WHERE id_publicacion_informacion=?  AND id_usuario_emprendedor=? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_publicacion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $publicacion = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $publicacion;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerPublicacionDelUsuarioEmprendedor: " . $e->getMessage());
    }
}

function bajaArchivosPublicaciones($conexion, $id_publicacion, $archivos_eliminados)
{
    try {
        $archivos = implode("','", $archivos_eliminados);
        $sentenciaSQL = $conexion->prepare("DELETE FROM archivo_publicacion_informacion
        WHERE nombre_archivo IN ('" . $archivos . "') AND id_publicacion_informacion=?");
        $sentenciaSQL->bindParam(1, $id_publicacion, PDO::PARAM_INT);
        $resultado = $sentenciaSQL->execute();
        return  $resultado;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaArchivosPublicaciones: " . $e->getMessage());
    }
}

function modificarPublicacionInformacion($conexion, $descripcion, $map_latitud, $map_longitud, $id_usuario_emprendedor, $id_publicacion, $nuevos_archivos, $archivos_eliminados, $nombre_carpeta)
{

    try {
        $fecha_modificacion = date("Y-m-d H:i:s");
        global $url_base_guardar_archivos;

        $conexion->beginTransaction();

        $sentenciaSQL = $conexion->prepare("UPDATE publicacion_informacion
                                            SET descripcion=?,map_latitud=?,map_longitud=? 
                                            WHERE id_publicacion_informacion = ? AND id_usuario_emprendedor = ?");
        $sentenciaSQL->bindParam(1, $descripcion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $map_latitud, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $map_longitud, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $id_publicacion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(5, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();

        $ruta = $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor . "/publicaciones_informacion/" . $nombre_carpeta;
        if (!empty($archivos_eliminados)) {
            if (bajaArchivosPublicaciones($conexion, $id_publicacion, $archivos_eliminados)) {
                for ($i = 0; $i < count($archivos_eliminados); $i++) {
                    $ruta_archivo = $ruta . "/" . $archivos_eliminados[$i];
                    if (is_file($ruta_archivo)) {
                        unlink($ruta_archivo);
                    } else {
                        throw new Exception("No se pudo eliminar los archivos de la publicaciones");
                    }
                }
            } else {
                throw new Exception("No se pudo eliminar la informacion de los archivos de la publicacion");
            }
        }

        if (!empty($nuevos_archivos)) {
            crearNuevaCarpeta($ruta);
            for ($i = 0; $i < count($nuevos_archivos['name']); $i++) {
                $extencion_archivo = pathinfo($nuevos_archivos['name'][$i], PATHINFO_EXTENSION);
                $nombre_archivo = str_replace([" ", ":"], ["_", "-"], $fecha_modificacion) . "_" . $i . "." . $extencion_archivo;
                if (move_uploaded_file($nuevos_archivos['tmp_name'][$i], $ruta . "/" . $nombre_archivo)) {
                    altaArchivosPublicaciones($conexion, $nombre_carpeta, $nombre_archivo, $id_publicacion);
                } else {
                    eliminarCarpetaConArchivos($ruta);
                    throw new Exception("No se pudo guardar los archivos de la publicacion");
                }
            }
        }

        if (is_dir($ruta)) {
            $contenido = array_diff(scandir($ruta), array('.', '..'));
            if (empty($contenido)) {
                rmdir($ruta);
            }
        }

        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta modificarPublicacionInformacion: " . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}

function modificarPublicacionInformacionPorAdmin($conexion, $fecha_modificada, $descripcion, $map_latitud, $map_longitud, $id_usuario_emprendedor, $id_publicacion, $nuevos_archivos, $archivos_eliminados, $nombre_carpeta_actual)
{

    try {

        $fecha_subida = date("Y-m-d H:i:s");
        global $url_base_guardar_archivos;
        $conexion->beginTransaction();

        $sentenciaSQL = $conexion->prepare("UPDATE publicacion_informacion
                                            SET fecha_publicacion=?,descripcion=?,map_latitud=?,map_longitud=? 
                                            WHERE id_publicacion_informacion = ? AND id_usuario_emprendedor = ?");
        $sentenciaSQL->bindParam(1, $fecha_modificada, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $descripcion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $map_latitud, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $map_longitud, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $id_publicacion, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(6, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();

        $ruta = $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor . "/publicaciones_informacion/" . $nombre_carpeta_actual;

        if (!empty($archivos_eliminados)) {
            if (bajaArchivosPublicaciones($conexion, $id_publicacion, $archivos_eliminados)) {
                for ($i = 0; $i < count($archivos_eliminados); $i++) {
                    $ruta_archivo = $ruta . "/" . $archivos_eliminados[$i];
                    if (is_file($ruta_archivo)) {
                        unlink($ruta_archivo);
                    } else {
                        throw new Exception("No se pudo eliminar los archivos de la publicaciones");
                    }
                }
            } else {
                throw new Exception("No se pudo eliminar la informacion de los archivos de la publicacion");
            }
        }
        if (!empty($nuevos_archivos)) {
            crearNuevaCarpeta($ruta);
            for ($i = 0; $i < count($nuevos_archivos['name']); $i++) {
                $extencion_archivo = pathinfo($nuevos_archivos['name'][$i], PATHINFO_EXTENSION);
                $nombre_archivo = str_replace([" ", ":"], ["_", "-"], $fecha_subida) . "_" . $i . "." . $extencion_archivo;
                if (move_uploaded_file($nuevos_archivos['tmp_name'][$i], $ruta . "/" . $nombre_archivo)) {
                    altaArchivosPublicaciones($conexion, $nombre_carpeta_actual, $nombre_archivo, $id_publicacion);
                } else {
                    eliminarCarpetaConArchivos($ruta);
                    throw new Exception("No se pudo guardar los archivos de la publicacion");
                }
            }
        }


        if (is_dir($ruta)) {
            $contenido = array_diff(scandir($ruta), array('.', '..'));
            if (empty($contenido)) {
                rmdir($ruta);
            }
            $nombre_carpeta_nueva = str_replace([" ", ":"], ["_", "-"], $fecha_modificada);

            if ($nombre_carpeta_actual != $nombre_carpeta_nueva) {
                modificarNombreCarpetaArchivoPublicacion($conexion, $nombre_carpeta_nueva, $id_publicacion);
                $ruta_nueva =  $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor . "/publicaciones_informacion/" . $nombre_carpeta_nueva;
                if (!rename($ruta, $ruta_nueva)) {
                    throw new Exception("Error al intentar renombrar la carpeta");
                }
            }
        }

        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta modificarPublicacionInformacionPorAdmin: " . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}

function modificarNombreCarpetaArchivoPublicacion($conexion, $nombre_carpeta_nueva, $id_publicacion)
{
    try {
        $sentenciaSQL = $conexion->prepare("UPDATE archivo_publicacion_informacion 
                                            SET nombre_carpeta=? 
                                            WHERE id_publicacion_informacion=?");
        $sentenciaSQL->bindParam(1, $nombre_carpeta_nueva, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $id_publicacion, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarNombreCarpetaArchivoPublicacion: " . $e->getMessage());
    }
}


function obtenerPublicacionesDelPerfilUsuarioEmprendedor($conexion, $id_usuario_emprendedor, $condicion_limit)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * FROM usuario_emprendedor up 
                                            INNER JOIN publicacion_informacion p ON up.id_usuario_emprendedor = p.id_usuario_emprendedor
                                            WHERE up.id_usuario_emprendedor=?
                                            ORDER BY p.fecha_publicacion DESC " . $condicion_limit);
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $publicaciones = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $publicaciones;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerPublicacionesDelPerfilUsuarioEmprendedor: " . $e->getMessage());
    }
}


function obtenerListaUltimasPublicacionesGeneral($conexion, $condicion_limit)
{
    try {
        $activado = 1;
        $baneado = 0;
        $sentenciaSQL = $conexion->prepare("SELECT p.*, up.nombre_emprendimiento, up.foto_perfil_nombre
                                            FROM publicacion_informacion p 
                                            INNER JOIN (
                                                SELECT id_usuario_emprendedor, MAX(fecha_publicacion) AS ultima_publicacion
                                                FROM publicacion_informacion
                                                GROUP BY id_usuario_emprendedor
                                            ) ultima_publicacion_emprendedor ON p.id_usuario_emprendedor = ultima_publicacion_emprendedor.id_usuario_emprendedor
                                            INNER JOIN usuario_emprendedor up ON p.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario
                                            WHERE p.fecha_publicacion = ultima_publicacion_emprendedor.ultima_publicacion AND u.activado = ? AND baneado=? 
                                            ORDER BY p.fecha_publicacion DESC " . $condicion_limit);
        $sentenciaSQL->bindParam(1, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $baneado, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $listaPublicacion = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaPublicacion;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaUltimasPublicacionesGeneral: " . $e->getMessage());
    }
}


function verificarSiLaPublicacionExiste($conexion, $id_publicacion)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM publicacion_informacion p
                                            WHERE p.id_publicacion_informacion = ?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $id_publicacion, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiLaPublicacionExiste:" . $e->getMessage());
    }
}
