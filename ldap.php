<?php

/**
 * Clase para manejar la autenticación contra un servidor LDAP.
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
     * Grupo al que pertenecería el usuario $user.
     * @var string
     */
    private $group;
    /**
     * Unidad organizativa en la que están los usuarios.
     * @var string
     */
    private $userOrg = 'usuarios';
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
     * Constructor de nuestra clase. Se le puede pasar una configuración
     * opcional por si se quisiera modificar los valores de conexión por
     * defecto.
     *
     * @var array $config Configuración para inicializar el objeto. Acepta:
     * 'host', 'port' y 'basedn'.
     */
    public function __construct($config = [])
    {
        $host = false;
        $port = false;
        $basedn = false;
        extract($config, EXTR_IF_EXISTS);
        $this->setHost($host);
        $this->setPort($port);
        $this->setBasedn($basedn);
    }

    /**
     * Los grupos que se deben comprobar para otorgar el permiso correcto.
     *
     * @return array Es un array 'clave' => 'valor' que contendrá los nombres
     * de los grupos y la unidas organizativas a la que pertenecen de la
     * siguiente manera:
     *
     * [
     *      'grupo1' => 'ou1',
     *      'grupo2' => 'ou1',
     *      'grupo3' => 'ou2',
     * ]
     *
     */
    public function permissions()
    {
        return [
            'admin' => 'inventario',
            'normal' => 'inventario',
            'editor' => 'inventario',
            'lector' => 'inventario',
            'invitado' => 'inventario',
            'grupo1' => 'grupos2',
        ];
    }

    /**
     * Establece un valor para la propiedad host.
     * @param string|false $val El nuevo valor para host. Si es false no se hará
     * el cambio.
     */
    private function setHost($val)
    {
        $this->assign($val, 'host');
    }

    /**
     * Establece un valor para la propiedad port.
     * @param string|false $val El nuevo valor para port. Si es false no se hará
     * el cambio.
     */
    private function setPort($val)
    {
        $this->assign($val, 'port');
    }

    /**
     * Establece un valor para la propiedad basedn.
     * @param string|bool $val El nuevo valor para basedn. Si es false no se
     * hará el cambio.
     */
    private function setBasedn($val)
    {
        $this->assign($val, 'host');
    }

    /**
     * Establece un valor para la propiedad group.
     * @param string|bool $val El nuevo valor para group. Si es false no se
     * hará el cambio.
     */
    public function setGroup($val)
    {
        $this->assign($val, 'group');
    }

    /**
     * Asigna un valor a una propiedad si dicho valor no es falso.
     * @param  string|false $val  Valor que se asigna.
     * @param  string       $name Nombre de la variable.
     */
    private function assign($val, $name)
    {
        if ($val) {
            $this->$name = $val;
        }
    }

    /**
     * Devuelve el valor de la propiedad group.
     * @return string Grupo al que pertenece.
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Devuelve el valor de la propiedad user.
     * @return string Nombre de usuario.
     */
    public function getUsername()
    {
        return $this->user;
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
            $bind = ldap_bind($this->ldap, "cn={$user},ou={$this->userOrg},{$this->basedn}", $pass);
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
     * Comprueba que el usuario está o no en un grupo.
     *
     * @return bool Falso si no está en el grupo (o no se pudo comprobar)
     * o verdadero si sí lo está.
     */
    public function checkGroup($group = 'grupo1', $organization = 'grupos2')
    {
        if (!$this->binded) {
            return false;
        }
        $filter = "(&(objectClass=person)(cn=$this->user)(memberOf=cn=$group,ou=$organization,$this->basedn))";
        $searchResult = ldap_search($this->ldap, $this->basedn, $filter);
        $entries = ldap_get_entries($this->ldap, $searchResult);
        return $entries['count'] > 0;
    }

    /**
     * Obtiene el permiso del usuario.
     *
     * @return bool Verdadero si se obtuvo un grupo.
     * @throws Exception Si no pertenece a ningún grupo.
     */
    private function getPermission()
    {
        foreach ($this->permissions() as $group => $org) {
            if ($this->checkGroup($group, $org)) {
                $this->group = $group;
                return true;
            }
        }
        throw new \Exception('No se pudo comprobar la pertenencia a ningún Grupo.', 1);
    }

    public function login($username, $password)
    {
        $username = $this->sanitize($username);
        $password = $this->sanitize($password);
        $this->bind($username, $password);
        return $this->getPermission();
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

    /**
     * [sanitize description]
     * @param  [type] $var [description]
     * @return [type]      [description]
     */
    private function sanitize($var)
    {
        return ldap_escape($var, null, LDAP_ESCAPE_FILTER);
    }
}
