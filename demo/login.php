<?php
session_start();

include_once 'controller.php';
include_once '../LDAP.php';

$ldap = new LDAP();
$controller = new LDAPController($ldap);
try {
    $controller->login();
} catch (\Exception $e) {
    ?>
        <div>
            <?= 'Error: ' . $e->getMessage() ?>
        </div>
    <?php
}
?>

<form action="" method="post">
    <input type="text" name="username" value="">
    <input type="password" name="password" value="">
    <button type="submit" name="button">Logear</button>
</form>
