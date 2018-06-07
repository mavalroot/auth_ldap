<?php

include_once 'ldap.php';

$ldap = new LDAP();
$ldap->conectar();
var_dump($ldap->bind('juan', '123456'));
var_dump($ldap->checkGroup());
