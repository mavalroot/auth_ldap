<?php

/**
 *
 */
class LDAP
{
    /**
     * Nombre de usuario.
     * @var string
     */
    private $user;
    /**
     * Contraseña del usuario.
     * @var string
     */
    private $pass;
    /**
     * Host del servidor LDAP.
     * @var string.
     */
    private $host = '192.168.0.104';
    /**
     * Puerto del servidor LDAP.
     * @var string
     */
    private $port = '389';
    /**
     * DN Base.
     * @var string
     */
    private $basedn = 'dc=cementeriochipiona,dc=com';
    /**
     * Conexión con el servidor LDAP.
     * @var resource|bool
     */
    private $ldap = false;
    /**
     * Indica si se ha ligado con un usuario y contraseña o no.
     * @var bool
     */
    private $binded = false;


    /**
     * Constructor de nuestra clase.
     */
    public function __construct($config = [])
    {
        $host = false;
        $port = false;
        $basedn = false;
        extract($config, EXTR_IF_EXISTS);
        if ($host) {
        }
    }

    private function setHost($val)
    {
        $this->assign($val, 'host');
    }

    private function setBasedn($val)
    {
        $this->assign($val, 'host');
    }

    private function setPort($val)
    {
        $this->assign($val, 'port');
    }

    private function assign($val, $name)
    {
        if ($val) {
            $this->$name = $val;
        }
    }

    /**
     * Hace la conexión a un servidor LDAP.
     *
     * @throws Exception Si no se pudo hacer la conexión.
     */
    public function conectar()
    {
        $ldap = ldap_connect($this->host, $this->port);
        if (!($this->ldap = $ldap)) {
            throw new \Exception('No se pudo conectar al servidor.', 1);
        }
    }

    /**
     * Autentica un usuario con una contraseña en un servidor LDAP.
     *
     * @param  string $user Nombre de usuario.
     * @param  string $pass Contraseña.
     * @return bool
     * @throws Exception Si no se pudo hacer la conexión.
     */
    public function bind($user, $pass)
    {
        if (!$this->ldap) {
            $this->conectar();
        }
        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
        if ($this->ldap) {
            $bind = ldap_bind($this->ldap, "cn={$user},ou=usuarios,{$this->basedn}", $pass);
            if ($bind) {
                $this->user = $user;
                $this->pass = $pass;
                $this->binded = true;
                return true;
            }
        }
        throw new \Exception('No se pudo conectar al servidor.', 1);
    }

    /**
     * Cierra la conexión actual.
     *
     * @return mixed
     */
    public function desconectar()
    {
        if (ldap_unbind($this->ldap)) {
            return $this->reset();
        }
        return false;
    }

    /**
     * Comprueba que el usuario está o no en un grupo.
     *
     * @return bool
     * @throws Exception Si no hay un usuario y contraseña bindeado.
     */
    public function checkGroup($group = 'grupo1', $organization = 'grupos2')
    {
        $filter = "(&(objectClass=person)(cn=$this->user)(memberOf=cn=$group,ou=$organization,$this->basedn))";
        if (!$this->binded) {
            throw new \Exception('No tienes permiso.', 1);
        }
        $searchResult = ldap_search($this->ldap, $this->basedn, $filter);
        $entries = ldap_get_entries($this->ldap, $searchResult);
        return $entries['count'] > 0;
    }

    /**
     * Resetea los valores dinámicos del modelo.
     * No se resetean: host, port y basedn, porque se entiende que no cambian
     * de una consulta a otra.
     *
     * @return bool Siempre devuelve true al terminar.
     */
    public function reset()
    {
        $this->ldap = false;
        $this->binded = false;
        $this->user = null;
        $this->pass = null;
        return true;
    }
}
