<?php

function login_usuario($conexion, $email, $password, $dispositivo)
{
    try {
        $usuario = obtenerDatosUsuarioPorEmail($conexion, $email);
        if (empty($usuario) || !password_verify($password, $usuario["contrasenia"])) {
            throw new Exception("El Email y/o la contraseña son incorrectos");
        }
        if ($usuario['activado'] == 0) {
            global $url_base;
            $url = $url_base . "/paginas/activar_cuenta/pagina_enviar_email_activacion.php";
            if ($dispositivo == 'web') {
                $cuerpo = "Si no ha recibido un correo electrónico para activar su cuenta o ha desactivado su cuenta, entre al siguiente <a href='$url'>enlace</a> para volver a activarla.";
            } else if ($dispositivo == 'mobile') {
                $cuerpo = "Si no ha recibido un correo electrónico para activar su cuenta o ha desactivado su cuenta, puede volver a activarla atraves de la pantalla Activar Cuenta";
            }

            throw new Exception("La cuenta del usuario no se encuentra activa." . $cuerpo);
        }
        if ($usuario['baneado'] == 1) {
            throw new Exception("La cuenta del usuario se encuentra baneada.");
        }
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta login_usuario:"  . $e->getMessage());
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function obtenerDatosUsuarioPorEmail($conexion, $email)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT u.*,ue.id_usuario_emprendedor,ue.nombre_emprendimiento FROM usuario u 
                                            LEFT JOIN usuario_emprendedor ue ON u.id_usuario =ue.id_usuario 
                                            WHERE u.email= ? LIMIT 1");
        $sentenciaSQL->bindParam(1, $email, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerDatosUsuarioPorEmail:" . $e->getMessage());
    }
}

function obtenerDatosUsuarioPorIdUsuario($conexion, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario 
                                            WHERE id_usuario =? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerDatosUsuarioPorIdUsuario:" . $e->getMessage());
    }
}

function altaUsuario($conexion, $nombre_usuario, $nombres, $apellidos, $email, $password, $token, $tipo_usuario, $nombre_empredimiento)
{
    try {
        $fecha = date("Y-m-d H:i:s");
        $password = password_hash($password, PASSWORD_DEFAULT);
        $conexion->beginTransaction();
        $sentenciaSQL = $conexion->prepare("INSERT INTO usuario(nombre_usuario,nombres,apellidos,email,contrasenia,fecha,token,id_tipo_usuario)
                                            VALUES(?,?,?,?,?,?,?,?)");
        $sentenciaSQL->bindParam(1, $nombre_usuario, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $nombres, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $apellidos, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $password, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(6, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(7, $token, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(8, $tipo_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $id_usuario = $conexion->lastInsertid();

        if ($id_usuario !== null) {
            if ($tipo_usuario == 2) {
                altaUsuarioEmprendedor($conexion, $nombre_empredimiento, $id_usuario);
            } else {
                if ($tipo_usuario != 1) {
                    throw new Exception("El tipo de usuario no es valido");
                }
            }

            enviarDatosActivacion($id_usuario, $email, $token);
        } else {
            throw new Exception("No se pudo obtener la informacion necesaria para registrar al usuario comun");
        }
        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta altaUsuario:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}

function altaUsuarioPorAdmin($conexion, $nombre_usuario, $nombres, $apellidos, $email, $password, $token, $tipo_usuario, $nombre_empredimiento, $activado, $baneado)
{
    try {

        $fecha = date("Y-m-d H:i:s");
        $password = password_hash($password, PASSWORD_DEFAULT);
        $conexion->beginTransaction();
        $sentenciaSQL = $conexion->prepare("INSERT INTO usuario(nombre_usuario, nombres, apellidos,email, contrasenia, fecha,token, activado, baneado,id_tipo_usuario) 
                                            VALUES (?,?,?,?,?,?,?,?,?,?)");
        $sentenciaSQL->bindParam(1, $nombre_usuario, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $nombres, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $apellidos, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $password, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(6, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(7, $token, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(8, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(9, $baneado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(10, $tipo_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $id_usuario = $conexion->lastInsertid();

        if ($id_usuario !== null) {
            if ($tipo_usuario == 2) {
                altaUsuarioEmprendedor($conexion, $nombre_empredimiento, $id_usuario);
            } else {
                if ($tipo_usuario != 1) {
                    throw new Exception("El tipo de usuario no es valido");
                }
            }
            if (!$activado) {
                enviarDatosActivacion($id_usuario, $email, $token);
            }
        } else {
            throw new Exception("No se pudo obtener la informacion necesaria para registrar al usuario");
        }
        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta altaUsuarioPorAdmin:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}

function altaUsuarioEmprendedor($conexion, $nombre_empredimiento, $id_usuario)
{
    try {

        $sentenciaSQL = $conexion->prepare("INSERT INTO usuario_emprendedor(nombre_emprendimiento, id_usuario) VALUES (?,?)");
        $sentenciaSQL->bindParam(1, $nombre_empredimiento, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un problema en la base de datos: " . $e->getMessage());
    }
}

function bajaUsuario($conexion, $id_usuario)
{
    try {
        global $url_base_guardar_archivos;
        $conexion->beginTransaction();
        $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
        if (!empty($usuario_emprendedor)) {
            $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];
            $ruta = $url_base_guardar_archivos . "/uploads/" . $id_usuario_emprendedor;
            eliminarCarpetaRecursiva($ruta);
        }
        $sentenciaSQL = $conexion->prepare("DELETE 
                                            FROM usuario 
                                            WHERE id_usuario = ?");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta bajaUsuario:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}

function laCuentaDelUsuarioExiste($conexion, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM  usuario
                                            WHERE id_usuario = ? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta laCuentaDelUsuarioExiste: " . $e->getMessage());
    }
}


function enviarDatosActivacion($id_usuario, $email, $token)
{
    try {
        global $url_base;
        $url = $url_base . "/paginas/activar_cuenta/pagina_activar_cuenta.php?id=$id_usuario&token=$token";
        $asunto = "Activar cuenta del usuario";
        $cuerpo = "<p>Para activar su cuenta, haga clic en el siguiente <a href='$url'>enlace</a>.</p>";
        enviarCorreo($email, $asunto, $cuerpo);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function verificarSiEmailEstaDisponible($conexion, $email)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario
                                            WHERE email=?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $email, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiEmailEstaDisponible:" . $e->getMessage());
    }
}

function verificarSiNombreUsuarioEstaDisponible($conexion, $nombre_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario
                                            WHERE nombre_usuario=?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $nombre_usuario, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiNombreUsuarioEstaDisponible:" . $e->getMessage());
    }
}

function verificarSiNombreEmprendedorEstaDisponibles($conexion, $nombre_empredimiento)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario_emprendedor
                                            WHERE nombre_emprendimiento=?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $nombre_empredimiento, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiNombreEmprendedorEstaDisponibles:" . $e->getMessage());
    }
}

function verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * FROM usuario u
                                            INNER JOIN usuario_emprendedor up ON u.id_usuario=up.id_usuario
                                            WHERE u.id_usuario = ?
                                            LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiEsUsuarioEmprendedor:" . $e->getMessage());
    }
}

function modificarNombreYApellidoUsuario($conexion, $id_usuario, $nombres, $apellidos)
{
    try {
        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET nombres=?,apellidos=?
                                            WHERE id_usuario =?");

        $sentenciaSQL->bindParam(1, $nombres, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $apellidos, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarNombreYApellidoUsuario:" . $e->getMessage());
    }
}

function modificarContraseniaUsuario($conexion, $id_usuario, $password)
{
    try {
        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET contrasenia=?
                                            WHERE id_usuario =?");

        $sentenciaSQL->bindParam(1, $password, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarContraseniaUsuario:" . $e->getMessage());
    }
}

function modificarDatosUsuario($conexion, $id_usuario, $nombre_usuario, $nombres, $apellidos, $email, $fecha, $baneado)
{
    try {
        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET nombre_usuario=?,nombres=?,apellidos=?,email=?,fecha=?,baneado=?
                                            WHERE id_usuario =?");

        $sentenciaSQL->bindParam(1, $nombre_usuario, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $nombres, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $apellidos, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $fecha, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(6, $baneado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(7, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarDatosUsuario:" . $e->getMessage());
    }
}


function enviarEmailCrearNuevaPassword($email, $token_password, $id_usuario)
{
    try {
        global $url_base;
        $url = $url_base .  "/paginas/recuperar_cuenta_password/pagina_cambiar_password.php?id=$id_usuario&token=$token_password";
        $asunto = "Confirmacion de recuperar contraseña";
        $cuerpo = "<p>Para completar el proceso de creación de una nueva contraseña, haga clic <a href='$url'>aquí</a>.</p>";
        enviarCorreo($email, $asunto, $cuerpo);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}


function enviarEmailPasswordOlvidado($conexion, $email, $token_password, $id_usuario)
{

    try {
        $contrasenia_pedido = 1;
        $conexion->beginTransaction();
        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET token_contrasenia=?,contrasenia_pedido=?
                                            WHERE id_usuario =?");

        $sentenciaSQL->bindParam(1, $token_password, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $contrasenia_pedido, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $id_usuario, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        enviarEmailCrearNuevaPassword($email, $token_password, $id_usuario);

        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta enviarEmailPasswordOlvidado:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}


function verificarNuevoEmailIngresado($conexion, $id_usuario, $nuevo_email, $token_email)
{
    try {
        $conexion->beginTransaction();
        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET nuevo_email=?,token_nuevo_email=?
                                            WHERE id_usuario =?");

        $sentenciaSQL->bindParam(1, $nuevo_email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $token_email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        enviarEmailConfirmacionCambioEmail($id_usuario, $nuevo_email, $token_email);

        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta verificarNuevoEmailIngresado:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}


function modificarEmailUsuario($conexion, $id_usuario, $email)
{
    try {
        $token_nuevo_email = NULL;
        $nuevo_email = NULL;

        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET email=?,token_nuevo_email=?,nuevo_email=?
                                            WHERE id_usuario =?");

        $sentenciaSQL->bindParam(1, $email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $token_nuevo_email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $nuevo_email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarEmailUsuario:" . $e->getMessage());
    }
}


function obtenerNuevoEmailUsuario($conexion, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT nuevo_email 
                                            FROM usuario
                                            WHERE id_usuario =?");

        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $usuario['nuevo_email'];
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerNuevoEmailUsuario:" . $e->getMessage());
    }
}

function enviarEmailConfirmacionCambioEmail($id_usuario, $nuevo_email, $token)
{
    try {
        global $url_usuario;
        $url = $url_usuario . "/configuracion/pagina_cambio_email.php?id=$id_usuario&token=$token";
        $asunto = "Confirmacion de cambio de Correo electronico";
        $cuerpo = "Para confirmar el cambio de correo electrónico en su cuenta, haga clic <a href='$url'>aquí</a>.";
        enviarCorreo($nuevo_email, $asunto, $cuerpo);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)
{
    try {
        $activado = 1;
        $baneado = 0;
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM usuario 
                                            WHERE id_usuario = ? AND id_tipo_usuario=? AND activado=? AND baneado=?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $tipo_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $baneado, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiEsUsuarioValido:" . $e->getMessage());
    }
}

function validarIdToken($conexion, $id, $token)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario
                                            WHERE id_usuario=? AND token=?
                                            LIMIT 1");
        $sentenciaSQL->bindParam(1, $id, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $token, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        if ($sentenciaSQL->rowCount() > 0) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta validarIdToken:" . $e->getMessage());
    }
}

function validarIdTokenEmail($conexion, $id, $token_nuevo_email)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario
                                            WHERE id_usuario=? AND token_nuevo_email=?
                                            LIMIT 1");
        $sentenciaSQL->bindParam(1, $id, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $token_nuevo_email, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        if ($sentenciaSQL->rowCount() > 0) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta validarIdTokenEmail:" . $e->getMessage());
    }
}

function validarTokenNuevaPassword($conexion, $token,$id)
{
    try {
        $contrasenia_pedido = 1;
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario
                                            WHERE id_usuario=? AND token_contrasenia=? AND contrasenia_pedido=?
                                            LIMIT 1");
        $sentenciaSQL->bindParam(1, $id, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $token, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $contrasenia_pedido, PDO::PARAM_INT);

        $sentenciaSQL->execute();
        if ($sentenciaSQL->rowCount() > 0) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta validarTokenNuevaPassword:" . $e->getMessage());
    }
}

function activarCuentaUsuario($conexion, $id_usuario)
{
    $activar = 1;
    $token = null;
    try {
        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET activado=?,token=?
                                            WHERE id_usuario=?");
        $sentenciaSQL->bindParam(1, $activar, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $token, PDO::PARAM_NULL);
        $sentenciaSQL->bindParam(3, $id_usuario, PDO::PARAM_INT);
        if (!$sentenciaSQL->execute()) {
            throw new Exception("Error no se pudo actualizar la informacion del usuario");
        }
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta activarCuentaUsuario:" . $e->getMessage());
    }
}

function obtenerTiposUsuarios($conexion)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * FROM tipo_usuario");
        $sentenciaSQL->execute();
        $tipos_Usuarios = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        if (empty($tipos_Usuarios)) {
            throw new Exception("No se encontaron resultados en la consulta para obtener los tipos de usuarios");
        }
        return $tipos_Usuarios;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerTiposUsuarios:" . $e->getMessage());
    }
}

function verificarSiEsUsuarioValidoPorEmail($conexion, $email)
{
    try {

        $activado = 1;
        $baneado = 0;
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario 
                                            WHERE email=? AND activado=? AND baneado=? LIMIT 1");
        $sentenciaSQL->bindParam(1, $email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $baneado, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta verificarSiEsUsuarioValidoPorEmail:" . $e->getMessage());
    }
}


function cuentaEstaActivada($conexion, $email)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT activado
                                            FROM usuario 
                                            WHERE email=? LIMIT 1");
        $sentenciaSQL->bindParam(1, $email, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        $usuario_activo = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        if ($usuario_activo['activado'] === 1) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cuentaEstaActivada:" . $e->getMessage());
    }
}

function modificarPasswordOlvidadoUsuario($conexion, $contrasenia, $token_contrasenia, $id_usuario)
{
    try {
        $token_contrasenia_original = NULL;
        $contrasenia_pedido = 0;

        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET contrasenia=?,token_contrasenia=?,contrasenia_pedido=? 
                                            WHERE id_usuario=? AND token_contrasenia=?");

        $sentenciaSQL->bindParam(1, $contrasenia, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $token_contrasenia_original, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $contrasenia_pedido, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(5, $token_contrasenia, PDO::PARAM_STR);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta modificarPasswordOlvidadoUsuario:" . $e->getMessage());
    }
}

function obtenerDatosUsuarioEmprendedorYUsuarioComun($conexion, $id_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM usuario u 
                                            LEFT JOIN usuario_emprendedor up ON u.id_usuario= up.id_usuario
                                            WHERE u.id_usuario = ?");
        $sentenciaSQL->bindParam(1, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerDatosUsuarioEmprendedorYUsuarioComun:" . $e->getMessage());
    }
}


function obtenerTodosDatosDeUsuarios($conexion, $condicion_where, $condicion_limit)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT u.*, case WHEN u.activado THEN 'Si' else 'No' END AS esta_activado,case WHEN u.baneado  THEN 'Si' else 'No' END AS esta_baneado,up.nombre_emprendimiento ,up.calificacion_emprendedor
                                            FROM usuario  u
                                            LEFT JOIN usuario_emprendedor up  ON up.id_usuario = u.id_usuario
                                            " . $condicion_where . " ORDER BY fecha DESC " . $condicion_limit . ";");

        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta obtenerTodosDatosDeUsuarios:" . $e->getMessage());
    }
}

function cantTotalUsuariosWheUsuario($conexion, $condicion_where)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT count(*) as cantidad_total
                                            FROM usuario u LEFT JOIN usuario_emprendedor up  ON up.id_usuario = u.id_usuario " . $condicion_where . ";");
        $sentenciaSQL->execute();
        $fila = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        $cant_total = $fila['cantidad_total'];
        return $cant_total;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta cantTotalUsuariosWheUsuario:" . $e->getMessage());
    }
}


function desactivarCuentaUsuario($conexion, $id_usuario)
{
    try {
        $desactivar = 0;
        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET activado=?
                                            WHERE id_usuario =?");
        $sentenciaSQL->bindParam(1, $desactivar, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la consulta desactivarCuentaUsuario:" . $e->getMessage());
    }
}

function volverEnviarEmailActivarCuentaUsuario($conexion,  $id_usuario, $email, $token)
{
    try {
        $conexion->beginTransaction();
        $sentenciaSQL = $conexion->prepare("UPDATE usuario 
                                            SET token=?
                                            WHERE id_usuario=?");
        $sentenciaSQL->bindParam(1, $token, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $id_usuario, PDO::PARAM_INT);
        $sentenciaSQL->execute();

        enviarDatosActivacion($id_usuario, $email, $token);
        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la consulta volverEnviarEmailActivarCuentaUsuario:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}
