<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title>Te has conectado</title>
    </head>
    <body>
        <h1>Te has conectado</h1>
        <?= var_dump($_SESSION) ?>
        <p>
            <a href="logout.php">Logout</a>
        </p>
    </body>
</html>
