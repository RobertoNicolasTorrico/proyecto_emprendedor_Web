<?php


function obtenerEstadosProducto($conexion)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * FROM estado_producto");
        $sentenciaSQL->execute();
        $estados = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        if (empty($estados)) {
            throw new Exception("No se encontaron resultados en la consulta para obtener los estados del producto");
        }
        return $estados;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerEstadosProducto:" . $e->getMessage());
    }
}

function altaPublicacionProducto($conexion, $nombre_producto, $descripcion, $precio, $stock, $categoria_producto, $id_usuario_emprendedor, $archivos)
{
    try {
        $fecha = date("Y-m-d H:i:s");
        global $url_base_guardar_archivos;
        $conexion->beginTransaction();
        $sentenciaSQL = $conexion->prepare("INSERT INTO publicacion_producto(nombre_producto, fecha_publicacion,descripcion, precio,stock,id_categoria_producto,id_usuario_emprendedor) 
                                                     VALUES(?,?,?,?,?,?,?)");
        $sentenciaSQL->bindParam(1, $nombre_producto, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $descripcion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $precio, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $stock, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(6, $categoria_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(7, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $id_publicacion_producto = $conexion->lastInsertid();

        $nombre_carpeta = str_replace([" ", ":"], ["_", "-"], $fecha);
        $ruta =  $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor . "/publicaciones_productos/" . $nombre_carpeta;
        crearNuevaCarpeta($ruta);
        for ($i = 0; $i < count($archivos['name']); $i++) {
            $extencion_archivo = pathinfo($archivos['name'][$i], PATHINFO_EXTENSION);
            $nombre_imagen = $nombre_carpeta . "_" . $i . "." . $extencion_archivo;
            if (move_uploaded_file($archivos['tmp_name'][$i], $ruta . "/" . $nombre_imagen)) {
                altaImagenProducto($conexion, $nombre_carpeta, $nombre_imagen, $id_publicacion_producto);
            } else {
                eliminarCarpetaConArchivos($ruta);
                throw new Exception("No se pudo guardar las imagenes del producto");
            }
        }
        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta altaPublicacionProducto:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($ruta);
    }
}

function modificarPublicacionProducto($conexion, $nombre_producto, $descripcion, $precio, $stock, $estado_producto, $categoria_producto, $id_usuario_emprendedor,  $id_producto, $archivo, $imagenes_eliminadas, $nombre_carpeta)
{
    try {
        $conexion->beginTransaction();
        global  $url_base_guardar_archivos;
        $fecha_modificacion = date("Y-m-d H:i:s");
        $ruta =  $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor . "/publicaciones_productos/" . $nombre_carpeta;
        $sentenciaSQL = $conexion->prepare("UPDATE publicacion_producto 
        SET nombre_producto=?,fecha_modificación=?,descripcion=?,precio=?,stock=?,id_estado_producto=?,id_categoria_producto=? 
        WHERE id_publicacion_producto =? AND id_usuario_emprendedor =?");
        $sentenciaSQL->bindParam(1, $nombre_producto, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $fecha_modificacion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $descripcion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $precio, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $stock, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(6, $estado_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(7, $categoria_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(8, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(9, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();


        if (!empty($imagenes_eliminadas)) {
            if (bajaImagenProducto($conexion, $id_producto, $imagenes_eliminadas)) {
                for ($i = 0; $i < count($imagenes_eliminadas); $i++) {
                    $ruta_archivo = $ruta . "/" . $imagenes_eliminadas[$i];
                    if (is_file($ruta_archivo)) {
                        unlink($ruta_archivo);
                    }
                }
            } else {
                throw new Exception("No se pudo eliminar la informacion de las imagenes del producto");
            }
        }
        if (!empty($archivo)) {
            $nombre_archivo = str_replace([" ", ":"], ["_", "-"], $fecha_modificacion);
            for ($i = 0; $i < count($archivo['name']); $i++) {
                $extencion_archivo = pathinfo($archivo['name'][$i], PATHINFO_EXTENSION);

                $nombre_imagen = $nombre_archivo . "_" . $i . "." . $extencion_archivo;
                $ruta_archivo = $ruta . "/" . $nombre_imagen;
                if (move_uploaded_file($archivo['tmp_name'][$i], $ruta_archivo)) {
                    altaImagenProducto($conexion, $nombre_carpeta, $nombre_imagen, $id_producto);
                } else {
                    throw new Exception("No se pudo guardar subir todas las nuevas imagenes del producto");
                }
            }
        }


        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta modificarPublicacionProducto:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}

function modificarPublicacionProductoAdmin($conexion, $nombre_producto, $descripcion, $precio, $stock, $estado_producto, $categoria_producto, $id_usuario_emprendedor,  $id_producto, $archivo, $imagenes_eliminadas, $nombre_carpeta, $fecha_publicacion, $fecha_modificada, $calificacion_producto)
{
    try {
        $conexion->beginTransaction();
        global $url_base_guardar_archivos;
        $ruta =  $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor . "/publicaciones_productos/" . $nombre_carpeta;
        $sentenciaSQL = $conexion->prepare("UPDATE publicacion_producto 
        SET nombre_producto=?,fecha_publicacion=?,fecha_modificación=?,descripcion=?,precio=?,stock=?,calificacion=?,id_estado_producto=?,id_categoria_producto=? 
        WHERE id_publicacion_producto =? AND id_usuario_emprendedor =?");
        $sentenciaSQL->bindParam(1, $nombre_producto, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $fecha_publicacion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $fecha_modificada, PDO::PARAM_STR);


        $sentenciaSQL->bindParam(4, $descripcion, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $precio, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(6, $stock, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(7, $calificacion_producto, PDO::PARAM_STR);

        $sentenciaSQL->bindParam(8, $estado_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(9, $categoria_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(10, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(11, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();


        if (!empty($imagenes_eliminadas)) {
            if (bajaImagenProducto($conexion, $id_producto, $imagenes_eliminadas)) {
                for ($i = 0; $i < count($imagenes_eliminadas); $i++) {
                    $ruta_archivo = $ruta . "/" . $imagenes_eliminadas[$i];
                    if (is_file($ruta_archivo)) {
                        unlink($ruta_archivo);
                    }
                }
            } else {
                throw new Exception("No se pudo eliminar la informacion de las imagenes del producto");
            }
        }
        if (is_null($fecha_modificada)) {
            $fecha_modificacion = date("Y-m-d H:i:s");
        } else {
            $fecha_modificacion = $fecha_modificada;
        }

        if (!empty($archivo)) {
            $nombre_archivo = str_replace([" ", ":"], ["_", "-"], $fecha_modificacion);
            for ($i = 0; $i < count($archivo['name']); $i++) {
                $extencion_archivo = pathinfo($archivo['name'][$i], PATHINFO_EXTENSION);

                $nombre_imagen = $nombre_archivo . "_" . $i . "." . $extencion_archivo;
                $ruta_archivo = $ruta . "/" . $nombre_imagen;
                if (move_uploaded_file($archivo['tmp_name'][$i], $ruta_archivo)) {
                    altaImagenProducto($conexion, $nombre_carpeta, $nombre_imagen, $id_producto);
                } else {
                    throw new Exception("No se pudo guardar subir todas las nuevas imagenes del producto");
                }
            }
        }


        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta modificarPublicacionProductoAdmin:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}

function altaImagenProducto($conexion, $nombre_carpeta, $nombre_archivo, $id_publicacion_producto)
{
    try {
        $sentenciaSQL = $conexion->prepare("INSERT INTO archivo_publicacion_producto(nombre_carpeta,nombre_archivo, id_publicacion_producto)
                                                    VALUES(?,?,?)");
        $sentenciaSQL->bindParam(1, $nombre_carpeta, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $nombre_archivo, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_publicacion_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta altaImagenProducto:" . $e->getMessage());
    }
}

function bajaImagenProducto($conexion, $id_producto, $imagenes_eliminadas)
{
    try {
        $imagenes = implode("','", $imagenes_eliminadas);
        $sentenciaSQL = $conexion->prepare("DELETE FROM archivo_publicacion_producto
        WHERE nombre_archivo IN ('" . $imagenes . "') AND id_publicacion_producto=?");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $resultado = $sentenciaSQL->execute();
        return  $resultado;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaImagenProducto:" . $e->getMessage());
    }
}

function obtenerDatosProducto($conexion, $id_producto)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT pp.*,ep.estado,up.nombre_emprendimiento,up.id_usuario,cp.nombre_categoria,u.activado,u.baneado
                                        FROM publicacion_producto pp
                                        INNER JOIN estado_producto ep ON pp.id_estado_producto = ep.id_estado_producto
                                        INNER JOIN categoria_producto cp ON pp.id_categoria_producto = cp.id_categoria_producto
                                        INNER JOIN usuario_emprendedor up ON pp.id_usuario_emprendedor = up.id_usuario_emprendedor
                                        INNER JOIN usuario u ON up.id_usuario = u.id_usuario
                                        WHERE id_publicacion_producto=? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $producto = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $producto;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerDatosProducto:" . $e->getMessage());
    }
}

function obtenerProductoDelUsuarioEmprendedor($conexion, $id_producto, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM publicacion_producto 
                                            WHERE id_publicacion_producto=? AND id_usuario_emprendedor=? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $producto = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $producto;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerProductoDelUsuarioEmprendedor:" . $e->getMessage());
    }
}

function obtenerListaImgProducto($conexion, $id_producto)
{
    try {
        $sentencia = $conexion->prepare("SELECT * 
                FROM  archivo_publicacion_producto 
                WHERE id_publicacion_producto=?;");
        $sentencia->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentencia->execute();
        $imagenes = $sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $imagenes;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaImgProducto:" . $e->getMessage());
    }
}

function obtenerListaProductoWhereLimit($conexion, $where, $limit, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT pp.*,ep.estado,cp.nombre_categoria,up.id_usuario
                                        FROM publicacion_producto pp
                                        INNER JOIN usuario_emprendedor up ON pp.id_usuario_emprendedor = up.id_usuario_emprendedor
                                        INNER JOIN estado_producto ep ON pp.id_estado_producto = ep.id_estado_producto
                                        INNER JOIN categoria_producto cp on pp.id_categoria_producto = cp.id_categoria_producto
                                        WHERE pp.id_usuario_emprendedor=? " . $where . "
                                        ORDER BY pp.id_publicacion_producto DESC " . $limit);
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $listaProductos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaProductos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaProductoWhereLimit:" . $e->getMessage());
    }
}

function cantTotalProductoWheUsuarioEmprendedor($conexion, $where, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM publicacion_producto pp
                                            WHERE pp.id_usuario_emprendedor=? " . $where);
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalProductoWheUsuarioEmprendedor:" . $e->getMessage());
    }
}


function obtenerListaBusquedaProductoWhereASCDESCLimitPerfil($conexion, $id_usuario_emprendedor, $where, $ASC_DESC, $limit)
{
    try {
        $estado_pausado = 2;

        $sentenciaSQL = $conexion->prepare("SELECT pp.*,up.nombre_emprendimiento,u.nombre_usuario,cp.nombre_categoria,ep.estado,up.foto_perfil_nombre         
                                            FROM publicacion_producto pp 
                                            INNER JOIN estado_producto ep ON pp.id_estado_producto = ep.id_estado_producto
                                            INNER JOIN categoria_producto cp ON pp.id_categoria_producto = cp.id_categoria_producto
                                            INNER JOIN usuario_emprendedor up ON pp.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario
                                            WHERE pp.id_usuario_emprendedor=? AND ep.id_estado_producto != ? " . $where . " ORDER BY " . $ASC_DESC . "  " . $limit);
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $estado_pausado, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $listaProductos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaProductos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaBusquedaProductoWhereASCDESCLimitPerfil:" . $e->getMessage());
    }
}


function cantTotalListaBusquedaProductoWhere($conexion, $where)
{
    try {
        $estado_disponible = 1;
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM publicacion_producto pp
                                            WHERE pp.id_estado_producto=? " . $where);
        $sentenciaSQL->bindParam(1, $estado_disponible, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalListaBusquedaProductoWhere:" . $e->getMessage());
    }
}

function elUsuarioLoPublico($conexion, $id_producto, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * FROM usuario u
        INNER JOIN usuario_emprendedor up ON u.id_usuario=up.id_usuario
        INNER JOIN publicacion_producto pp ON up.id_usuario_emprendedor = pp.id_usuario_emprendedor
        WHERE u.id_usuario =? AND pp.id_publicacion_producto=? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta elUsuarioLoPublico:" . $e->getMessage());
    }
}

function verificarSiElProductoEstaDisponible($conexion, $id_producto)
{
    try {
        $disponible = 1;
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM publicacion_producto pp 
                                            WHERE pp.id_publicacion_producto = ? AND pp.id_estado_producto = ?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $disponible, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiElProductoEstaDisponible:" . $e->getMessage());
    }
}

function verificarSiElProductoExiste($conexion, $id_producto)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM publicacion_producto pp 
                                            WHERE pp.id_publicacion_producto = ?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiElProductoExiste:" . $e->getMessage());
    }
}



function verificarSiElProductoEstaFinalizado($conexion, $id_producto)
{
    try {
        $estado_finalizado = 3;
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM publicacion_producto pp 
                                            WHERE pp.id_publicacion_producto = ? AND pp.id_estado_producto = ?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $estado_finalizado, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiElProductoEstaFinalizado:" . $e->getMessage());
    }
}



function cantTotalProductoDelEmprendedorPerfilWhere($conexion, $id_usuario_emprendedor, $where)
{
    try {
        $estado_pausado = 2;
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM publicacion_producto pp
                                            WHERE pp.id_usuario_emprendedor=?  AND pp.id_estado_producto != ? " . $where);
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $estado_pausado, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalProductoDelEmprendedorPerfilWhere:" . $e->getMessage());
    }
}

function carritoProductosDisponible($conexion, $lista_id_productos)
{
    try {
        $estado_disponible = 1;
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM publicacion_producto pp 
                                            INNER JOIN archivo_publicacion_producto app ON pp.id_publicacion_producto =app.id_publicacion_producto 
                                            WHERE pp.id_publicacion_producto IN ($lista_id_productos) AND pp.id_estado_producto=? 
                                            GROUP BY pp.id_publicacion_producto");
        $sentenciaSQL->bindParam(1, $estado_disponible, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        $listaProductosCarrito = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaProductosCarrito;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta carritoProductosDisponible:" . $e->getMessage());
    }
}


function obtenerListaTodosProductos($conexion, $condicion_where, $condicion_limit)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT pp.*,up.nombre_emprendimiento,cp.nombre_categoria ,ep.estado,up.id_usuario,up.foto_perfil_nombre
                                            FROM publicacion_producto pp
                                            INNER JOIN categoria_producto cp ON pp.id_categoria_producto = cp.id_categoria_producto
                                            INNER JOIN estado_producto ep ON pp.id_estado_producto = ep.id_estado_producto
                                            INNER JOIN usuario_emprendedor up ON pp.id_usuario_emprendedor = up.id_usuario_emprendedor 
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario " . $condicion_where . " ORDER BY pp.fecha_publicacion DESC " . $condicion_limit);
        $sentenciaSQL->execute();
        $listaProductos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaProductos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaTodosProductos:" . $e->getMessage());
    }
}
function cantTotalTodosProductos($conexion, $condicion_where)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM publicacion_producto pp
                                            INNER JOIN usuario_emprendedor up ON pp.id_usuario_emprendedor = up.id_usuario_emprendedor 
                                            " . $condicion_where);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalTodosProductos:" . $e->getMessage());
    }
}



function obtenerListaProductosDisponiblesParaIndex($conexion, $limit)
{
    try {
        $estado_disponible = 1;
        $sentenciaSQL = $conexion->prepare("SELECT pp.*,up.nombre_emprendimiento,u.nombre_usuario,cp.nombre_categoria,up.foto_perfil_nombre
                                            FROM publicacion_producto pp 
                                            INNER JOIN categoria_producto cp ON pp.id_categoria_producto = cp.id_categoria_producto
                                            INNER JOIN usuario_emprendedor up ON pp.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario
                                            WHERE pp.id_estado_producto=?  ORDER BY RAND() " . $limit);
        $sentenciaSQL->bindParam(1, $estado_disponible, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $listaProductos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaProductos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaProductosDisponiblesParaIndex:" . $e->getMessage());
    }
}

function obtenerListaBusquedaProductoWhereASCDESCLimit($conexion, $where, $ASC_DESC, $limit)
{
    try {
        $estado_disponible = 1;
        $usuario_activo = 1;

        $sentenciaSQL = $conexion->prepare("SELECT pp.*,up.nombre_emprendimiento,up.foto_perfil_nombre,up.id_usuario,u.nombre_usuario,cp.nombre_categoria
                                            FROM publicacion_producto pp 
                                            INNER JOIN categoria_producto cp ON pp.id_categoria_producto = cp.id_categoria_producto
                                            INNER JOIN usuario_emprendedor up ON pp.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            INNER JOIN usuario u ON up.id_usuario = u.id_usuario
                                            WHERE pp.id_estado_producto = ? AND u.activado = ?" . $where . " ORDER BY " . $ASC_DESC . " " . $limit);
        $sentenciaSQL->bindParam(1, $estado_disponible, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $usuario_activo, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $listaProductos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaProductos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaBusquedaProductoWhereASCDESCLimit:" . $e->getMessage());
    }
}


function bajaPublicacionProducto($conexion, $id_producto)
{
    try {
        $conexion->beginTransaction();

        global $url_base_guardar_archivos;

        $datos_carpeta = obtenerDatosCarpetaProducto($conexion, $id_producto);
        if (!empty($datos_carpeta)) {
            bajaTodosLosArchivosProductos($conexion, $id_producto);
            $ruta = $url_base_guardar_archivos . "/uploads/" . $datos_carpeta['id_usuario_emprendedor'] . "/publicaciones_productos/" . $datos_carpeta['nombre_carpeta'];
            eliminarCarpetaConArchivos($ruta);
        }
        $sentenciaSQL = $conexion->prepare("DELETE FROM publicacion_producto WHERE id_publicacion_producto = ?");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();


        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta bajaPublicacionProducto:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}
function obtenerDatosCarpetaProducto($conexion, $id_producto)
{
    try {
        $sentencia = $conexion->prepare("SELECT pp.id_usuario_emprendedor , api.nombre_carpeta
                                        FROM publicacion_producto pp
                                        INNER JOIN archivo_publicacion_producto api ON pp.id_publicacion_producto = api.id_publicacion_producto 
                                        WHERE pp.id_publicacion_producto = ? LIMIT 1");
        $sentencia->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentencia->execute();
        $nombre_carpeta = $sentencia->fetch(PDO::FETCH_ASSOC);
        return $nombre_carpeta;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerDatosCarpetaProducto:" . $e->getMessage());
    }
}
function bajaTodosLosArchivosProductos($conexion, $id_producto)
{
    try {
        $sentenciaSQL = $conexion->prepare("DELETE FROM archivo_publicacion_producto WHERE id_publicacion_producto = ?");
        $sentenciaSQL->bindParam(1, $id_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaTodosLosArchivosProductos:" . $e->getMessage());
    }
}

function modificarEstadoPublicacionesProductosDeEmprendedorAFinalizar($conexion, $id_usuario_emprendedor)
{
    try {
        $estado_producto = 3;
        $sentenciaSQL = $conexion->prepare("UPDATE publicacion_producto 
                                                SET id_estado_producto=?
                                                WHERE id_usuario_emprendedor =?");

        $sentenciaSQL->bindParam(1, $estado_producto, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarEstadoPublicacionesProductosDeEmprendedorAFinalizar:" . $e->getMessage());
    }
}
