<?php

function verificarEntradaDatosSession($campos_session)
{
    for ($i = 0; $i < count($campos_session); $i++) {
        if (!isset($_SESSION[$campos_session[$i]]) || empty($_SESSION[$campos_session[$i]])) {
            return false;
        }
    }
    return true;
}

function verificarDatosSessionUsuarioEmprendedor($conexion)
{
    global $url_base;
    $usuario_comun = 1;
    if (verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        if (verificarSiEsUsuarioValido($conexion, $_SESSION['id_usuario'], $_SESSION['tipo_usuario'])) {
            if ($_SESSION['tipo_usuario'] == $usuario_comun) {
                header("Location:" . $url_base . "/usuarios/index.php");
            }
        } else {
            unset($_SESSION['id_usuario']);
            unset($_SESSION['tipo_usuario']);
            header("Location:" . $url_base . "/paginas/iniciar_sesion/pagina_iniciar_sesion.php");
        }
    } else {
        header("Location:" . $url_base . "/paginas/iniciar_sesion/pagina_iniciar_sesion.php");
    }
}


function verificarDatosSessionUsuario($conexion)
{

    global $url_base;
    global $url_usuario;

    if (verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        if (verificarSiEsUsuarioValido($conexion, $_SESSION['id_usuario'], $_SESSION['tipo_usuario'])) {
            if ($_SESSION['tipo_usuario'] == 1 || $_SESSION['tipo_usuario'] == 2) {
                header("Location:" . $url_usuario . "/index.php");
            } else {
                unset($_SESSION['id_usuario']);
                unset($_SESSION['tipo_usuario']);
                header("Location:" . $url_base . "/paginas/iniciar_sesion/pagina_iniciar_sesion.php");
            }
        }
    }
}


function verificarDatosSessionUsuarioAdministrador($conexion)
{
    global $url_usuario_admin;
    if (verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        if (!verificarSiEsUsuarioAdminValido($conexion, $_SESSION['id_usuario_administrador'], $_SESSION['tipo_usuario_admin'])) {
            unset($_SESSION['id_usuario_administrador']);
            unset($_SESSION['tipo_usuario_admin']);
            header("Location:" . $url_usuario_admin . "/paginas/iniciar_sesion/pagina_iniciar_sesion_admin.php");
        }
    } else {
        header("Location:" . $url_usuario_admin . "/paginas/iniciar_sesion/pagina_iniciar_sesion_admin.php");
    }
}


function verificarSiUsuarioAdministradorInicioSesion($conexion)
{
    global $url_usuario_admin;

    if (verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
        if (verificarSiEsUsuarioAdminValido($conexion, $_SESSION['id_usuario_administrador'], $_SESSION['tipo_usuario_admin'])) {
            header("Location:" . $url_usuario_admin . "/index.php");
        } else {
            unset($_SESSION['id_usuario_administrador']);
            unset($_SESSION['tipo_usuario_admin']);
            header("Location:" . $url_usuario_admin . "/paginas/iniciar_sesion/pagina_iniciar_sesion_admin.php");
        }
    }
}
