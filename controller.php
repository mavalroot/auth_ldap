<?php

/**
 *
 */
class LdapController
{
    private $ldap;

    public function __construct($ldap)
    {
        $this->ldap = $ldap;
    }

    public function login()
    {
        $username = null;
        $password = null;
        extract($_POST, EXTR_IF_EXISTS);
        if (!isset($username, $password)) {
            return;
        }
        $ldap = $this->ldap;
        try {
            $ldap->login($username, $password);
        } catch (\Exception $e) {
            die('Error: ' . $e->getMessage());
        }
        $_SESSION['username'] = $ldap->getUsername();
        $_SESSION['group'] = $ldap->getGroup();
        $ldap->desconectar();
    }
}
