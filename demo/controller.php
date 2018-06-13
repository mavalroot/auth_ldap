<?php

include_once '../LDAP.php';

/**
 *
 */
class LDAPController
{
    /**
     * Objeto que contiene la información para la autenticación.
     *
     * @var LDAP
     */
    private $ldap;

    /**
     * Constructor de la clase.
     *
     */
    public function __construct()
    {
        $this->ldap = new LDAP();
    }

    /**
     * Autentica un usuario y una contraseña de un servidor LDAP.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function login()
    {
        $username = null;
        $password = null;
        extract($_POST, EXTR_IF_EXISTS);
        if (!isset($username, $password)) {
            return;
        }
        $ldap = $this->ldap;
        $ldap->login($username, $password);
        $_SESSION['username'] = $ldap->getUsername();
        $_SESSION['group'] = $ldap->getGroup();
        $ldap->desconectar();
        header('Location: index.php');
        exit;
    }
}
