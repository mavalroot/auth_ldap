<?php

include_once 'ldap.php';

$ldap = new LDAP();
try {
    $ldap->conectar();
    $ldap->bind('user1', '123456');
    $ldap->login();
    var_dump($ldap->getGroup());
} catch (\Exception $e) {
    die('Error: ' . $e->getMessage());
}
