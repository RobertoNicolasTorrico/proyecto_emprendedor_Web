<?php
function altaUsuarioAdmin($conexion, $nombre_usuario, $nombres, $apellidos, $email, $password)
{
    try {
        $fecha = date("Y-m-d H:i:s");
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sentenciaSQL = $conexion->prepare("INSERT INTO usuario_administrador(nombre_usuario,nombres,apellidos,email,contrasenia,fecha)
                                                    VALUES(?,?,?,?,?,?)");
        $sentenciaSQL->bindParam(1, $nombre_usuario, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $nombres, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $apellidos, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $password, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(6, $fecha, PDO::PARAM_STR);

        $sentenciaSQL->execute();
    } catch (PDOException $e) {

        throw new Exception("Hubo un error en la consulta altaUsuarioAdmin:" . $e->getMessage());
    }
}
function login_usuario_admin($conexion, $email, $password)
{
    try {
        $usuario = obtenerDatosUsuarioAdminPorEmail($conexion, $email);
        if (empty($usuario) || !password_verify($password, $usuario["contrasenia"])) {
            throw new Exception("Email y/o contraseña incorrectas.");
        }
        if ($usuario['activado'] == 0) {
            throw new Exception("La cuenta del usuario administrador no se encuentra activa.");
        }

        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la login_usuario_admin:" . $e->getMessage());
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}
function obtenerDatosUsuarioAdminPorEmail($conexion, $email)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario_administrador WHERE email=? LIMIT 1");
        $sentenciaSQL->bindParam(1, $email, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la obtenerDatosUsuarioAdminPorEmail:" . $e->getMessage());
    }
}


function verificarSiEsUsuarioAdminValido($conexion, $id_usuario_admin, $tipo_usuario)
{
    try {
        $activado = 1;

        $sentenciaSQL = $conexion->prepare("SELECT * FROM usuario_administrador 
                                            WHERE id_usuario_administrador= ? AND activado=? AND tipo_usuario_admin =?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $id_usuario_admin, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $tipo_usuario, PDO::PARAM_STR);

        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la verificarSiEsUsuarioAdminValido:" . $e->getMessage());
    }
}

function modificarDatosUsuarioAdmin($conexion, $id_usuario_administrador, $nombre_usuario, $nombres, $apellidos, $email)
{
    try {
        $sentenciaSQL = $conexion->prepare("UPDATE usuario_administrador 
                                            SET nombre_usuario=?,nombres=?,apellidos=?,email=?
                                            WHERE id_usuario_administrador =?");

        $sentenciaSQL->bindParam(1, $nombre_usuario, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $nombres, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $apellidos, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(4, $email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $id_usuario_administrador, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la modificarDatosUsuarioAdmin:" . $e->getMessage());
    }
}
function modificarContraseniaUsuarioAdmin($conexion, $id_usuario_administrador, $password)
{
    try {
        $sentenciaSQL = $conexion->prepare("UPDATE usuario_administrador 
                                            SET contrasenia=?
                                            WHERE id_usuario_administrador =?");

        $sentenciaSQL->bindParam(1, $password, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $id_usuario_administrador, PDO::PARAM_INT);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la modificarContraseniaUsuarioAdmin:" . $e->getMessage());
    }
}

function obtenerDatosUsuarioAdministrador($conexion, $id_usuario_administrador)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT * 
                                            FROM usuario_administrador 
                                            WHERE id_usuario_administrador = ? LIMIT 1");
        $sentenciaSQL->bindParam(1, $id_usuario_administrador, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        $usuario = $sentenciaSQL->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la obtenerDatosUsuarioAdministrador:" . $e->getMessage());
    }
}

function verificarSiEmailEstaDisponibleAdmin($conexion, $email)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario_administrador
                                            WHERE email=?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $email, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la verificarSiEmailEstaDisponibleAdmin:" . $e->getMessage());
    }
}


function verificarSiNombreUsuarioAdminEstaDisponible($conexion, $nombre_usuario)
{
    try {
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario_administrador
                                            WHERE nombre_usuario=?
                                            Limit 1");
        $sentenciaSQL->bindParam(1, $nombre_usuario, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la verificarSiNombreUsuarioAdminEstaDisponible:" . $e->getMessage());
    }
}

function verificarSiEsUsuarioValidoPorEmailAdmin($conexion, $email)
{
    try {

        $activado = 1;
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario_administrador WHERE email=? AND activado=? LIMIT 1");
        $sentenciaSQL->bindParam(1, $email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $activado, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        return $sentenciaSQL->rowCount() > 0;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la verificarSiEsUsuarioValidoPorEmailAdmin:" . $e->getMessage());
    }
}

function enviarEmailPasswordOlvidadoAdmin($conexion, $email, $token_password,$id_usuario_admin)
{

    try {
        $contrasenia_pedido = 1;
        $conexion->beginTransaction();
        $sentenciaSQL = $conexion->prepare("UPDATE usuario_administrador 
                                            SET token_contrasenia=?,contrasenia_pedido=?
                                            WHERE id_usuario_administrador =?");

        $sentenciaSQL->bindParam(1, $token_password, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $contrasenia_pedido, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(3, $id_usuario_admin, PDO::PARAM_STR);
        $sentenciaSQL->execute();
        enviarEmailCrearNuevaPasswordAdmin($email, $token_password,$id_usuario_admin);

        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw new Exception("Hubo un error en la enviarEmailPasswordOlvidadoAdmin:" . $e->getMessage());
    } catch (Exception $e) {
        $conexion->rollBack();
        throw new Exception($e->getMessage());
    }
}





function enviarEmailCrearNuevaPasswordAdmin($email, $token_password,$id_usuario_admin)
{
    try {
        global $url_usuario_admin;
        $url = $url_usuario_admin .  "/paginas/recuperar_cuenta_password/pagina_cambiar_password.php?id=$id_usuario_admin&token=$token_password";
        $asunto = "Confirmacion de recuperacion de contraseña";
        $cuerpo = "<p>Para completar el proceso de creación de una nueva contraseña, haga clic <a href='$url'>aquí</a>.</p>";
        enviarCorreo($email, $asunto, $cuerpo);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}




function validarTokenNuevaPasswordAdmin($conexion, $token, $id_admin)
{
    try {
        $contrasenia_pedido = 1;
        $sentenciaSQL = $conexion->prepare("SELECT *
                                            FROM usuario_administrador
                                            WHERE id_usuario_administrador =? AND token_contrasenia=? AND contrasenia_pedido=?
                                            LIMIT 1");

        $sentenciaSQL->bindParam(1, $id_admin, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(2, $token, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $contrasenia_pedido, PDO::PARAM_INT);
        $sentenciaSQL->execute();
        if ($sentenciaSQL->rowCount() > 0) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la validarTokenNuevaPasswordAdmin:" . $e->getMessage());
    }
}



function modificarPasswordOlvidadoUsuarioAdmin($conexion, $contrasenia, $token_contrasenia, $email)
{
    try {
        $token_contrasenia_original = NULL;
        $contrasenia_pedido = 0;

        $sentenciaSQL = $conexion->prepare("UPDATE usuario_administrador 
                                            SET contrasenia=?,token_contrasenia=?,contrasenia_pedido=? 
                                            WHERE email=? AND token_contrasenia=?");

        $sentenciaSQL->bindParam(1, $contrasenia, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(2, $token_contrasenia_original, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(3, $contrasenia_pedido, PDO::PARAM_INT);
        $sentenciaSQL->bindParam(4, $email, PDO::PARAM_STR);
        $sentenciaSQL->bindParam(5, $token_contrasenia, PDO::PARAM_STR);
        $sentenciaSQL->execute();
    } catch (PDOException $e) {
        throw new Exception("Hubo un error en la modificarPasswordOlvidadoUsuarioAdmin:" . $e->getMessage());
    }
}