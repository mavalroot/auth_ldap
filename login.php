<?php
session_start();

include_once 'controller.php';
include_once 'ldap.php';

$ldap = new LDAP();
$controller = new LdapController($ldap);
$controller->login();

var_dump($_SESSION);
?>

<form action="" method="post">
    <input type="text" name="username" value="">
    <input type="password" name="password" value="">
    <button type="submit" name="button">Logear</button>
</form>
