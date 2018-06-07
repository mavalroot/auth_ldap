<?php

/**
 *
 */
class LDAP
{
    private $user;
    private $pass;
    private $host = '192.168.0.104';
    private $port = '389';
    private $basedn = 'dc=cementeriochipiona,dc=com';
    public $ldap = false;


    public function __construct()
    {
    }

    /**
     * Hace la conexión a un servidor LDAP.
     */
    public function conectar()
    {
        $this->ldap = ldap_connect($this->host, $this->port) or die('No se pudo conectar.');
    }

    /**
     * Autentica un usuario con una contraseña en un servidor LDAP.
     * @param  string $user Nombre de usuario.
     * @param  string $pass Contraseña.
     * @return bool
     */
    public function bind($user, $pass)
    {
        if (!$this->ldap) {
            $this->conectar();
        }
        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
        if ($this->ldap) {
            $bind = ldap_bind($this->ldap, "uid={$user},ou=personas,{$this->basedn}", $pass) or die('No se pudo conectar.');
            if ($bind) {
                $this->user = $user;
                $this->pass = $pass;
                return true;
            }
        }
        return false;
    }

    /**
     * Cierra la conexión actual.
     * @return mixed
     */
    public function desconectar()
    {
        return ldap_unbind($this->ldap);
    }

    /**
     * Comprueba que el usuario está o no en un grupo.
     * @return bool
     */
    public function checkGroup()
    {
        $filter = "(&(CN=migrupo)(member=uid=$this->user,ou=personas,$this->basedn))";
        // echo var_dump($filter);
        $searchResult = ldap_search($this->ldap, $this->basedn, $filter);
        // echo var_dump($searchResult);
        $entries = ldap_get_entries($this->ldap, $searchResult);
        // echo var_dump($entries);
        $member = $entries['count'] > 0;
        return $member;
    }
}
