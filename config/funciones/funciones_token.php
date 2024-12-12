<?php

function generarToken()
{
    $token = md5(uniqid(mt_rand(), false));
    return $token;
}


function verificarUrlTokenId($id, $token)
{
    if (empty($id) || empty($token)) {
        throw new Exception("El campo id o token estan vacios");
    }
    $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);
    if ($token_tmp != $token) {
        throw new Exception("Las variables de la URL no coinciden");
    }
}

