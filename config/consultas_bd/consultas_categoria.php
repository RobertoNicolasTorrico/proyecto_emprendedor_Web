<?php

function altaCategoriaProducto($conexion, $nombre_categoria)
{
    try {
        $sentenciaSQL = $conexion->prepare("INSERT INTO categoria_producto(nombre_categoria) VALUES (?)");
        $sentenciaSQL->bindParam(1, $nombre_categoria, PDO::PARAM_STR);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta altaCategoriaProducto:" . $e->getMessage());
    }
}

function bajaCategoriaProducto($conexion, $id_categoria_producto)
{
    try {
        $sentenciaSQL = $conexion->prepare("DELETE FROM categoria_producto WHERE id_categoria_producto = ?");
        $sentenciaSQL->bindParam(1, $id_categoria_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta bajaCategoriaProducto:" . $e->getMessage());
    }
}

function cantTotalCategoriasWheCategoria($conexion, $condicion_where)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM categoria_producto cp " . $condicion_where);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalCategoriasWheCategoria:" . $e->getMessage());
    }
}


function obtenerCategoriasProducto($conexion)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM categoria_producto ORDER BY nombre_categoria ASC");
        $sentenciaSQL->execute();
        $categorias = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        if (empty($categorias)) {
            throw new Exception("No se encontaron resultados en la consulta para obtener las categorias de producto");
        }
        return $categorias;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerCategoriasProducto:" . $e->getMessage());
    }
}

function obtenerListaTodosCategoria($conexion, $condicion_where, $condicion_limit, $condicion_ASCDESC)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT cp.*, count(pp.id_categoria_producto) as cantidad_productos 
                                            FROM categoria_producto cp 
                                            LEFT JOIN publicacion_producto pp ON cp.id_categoria_producto = pp.id_categoria_producto " . $condicion_where . " GROUP BY cp.id_categoria_producto 
                                             " . $condicion_ASCDESC . " " . $condicion_limit);
        $sentenciaSQL->execute();
        $listaProductos = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $listaProductos;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerListaTodosCategoria:" . $e->getMessage());
    }
}


function obtenerCategoriaId($conexion, $id_categoria)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM categoria_producto 
                                            WHERE id_categoria_producto = ? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_categoria, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $categoria = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $categoria;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerCategoriaId:" . $e->getMessage());
    }
}

function modificarNombreCategoria($conexion, $nombre_categoria, $id_categoria_producto)
{
    try {

        $sentenciaSQL = $conexion->prepare("UPDATE categoria_producto 
                                            SET nombre_categoria=? 
                                            WHERE id_categoria_producto =?");
        $sentenciaSQL->bindParam(1, $nombre_categoria, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $id_categoria_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarNombreCategoria:" . $e->getMessage());
    }
}

function verificarSiTipoCategoriaEstaDisponible($conexion, $nombre_categoria)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM categoria_producto
                                            WHERE nombre_categoria=?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $nombre_categoria, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiTipoCategoriaEstaDisponible:" . $e->getMessage());
    }
}

function obtenerCategoriasDeProductosEmprendedor($conexion, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT cp.* 
                                            FROM categoria_producto cp 
                                            INNER JOIN publicacion_producto pp ON cp.id_categoria_producto = pp.id_categoria_producto
                                            INNER JOIN usuario_emprendedor up ON pp.id_usuario_emprendedor = up.id_usuario_emprendedor
                                            WHERE up.id_usuario=? GROUP BY cp.id_categoria_producto;");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $categoria = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $categoria;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerCategoriasDeProductosEmprendedor:" . $e->getMessage());
    }
}

function obtenerCategoriasDeProductosPerfilEmprendedor($conexion, $id_usuario_emprendedor)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT cp.* 
                                            FROM categoria_producto cp 
                                            INNER JOIN publicacion_producto pp ON cp.id_categoria_producto = pp.id_categoria_producto
                                            WHERE pp.id_usuario_emprendedor=? GROUP BY cp.id_categoria_producto;");
        $sentenciaSQL->bindParam(1, $id_usuario_emprendedor, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $categoria = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $categoria;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerCategoriasDeProductosPerfilEmprendedor:" . $e->getMessage());
    }
}

function obtenerCantidadProductosDeCategoria($conexion, $id_categoria_producto)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT COUNT(*) as cantidad_total
                                            FROM categoria_producto cp 
                                            INNER JOIN publicacion_producto pp ON cp.id_categoria_producto = pp.id_categoria_producto 
                                            WHERE cp.id_categoria_producto =?");
        $sentenciaSQL->bindParam(1, $id_categoria_producto, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerCantidadProductosDeCategoria:" . $e->getMessage());
    }
}

function laCategoriaSigueDisponible($conexion, $id_categoria)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM  categoria_producto
                                            WHERE id_categoria_producto = ? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_categoria, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta laCategoriaSigueDisponible: " . $e->getMessage());
    }
}
