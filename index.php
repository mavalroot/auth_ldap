<?php

include_once 'ldap.php';

$ldap = new LDAP();
try {
    $ldap->conectar();
    $ldap->bind('user1', '123456');
    var_dump($ldap->checkGroup('hola'));
    $ldap->desconectar();
    var_dump($ldap->checkGroup());
} catch (\Exception $e) {
    die('Error: ' . $e->getMessage());
}
