<?php

/**
 * @author María Valderrama Rodríguez <contact@mavalroot.es>
 * @copyright Copyright (c) 2018, María Valderrama Rodríguez
 *
 * Clase para manejar la autenticación contra un servidor LDAP.
 */
class LDAP
{
    /**
     * Nombre de usuario.
     *
     * @var string
     */
    protected $user;
    /**
     * Contraseña del usuario.
     *
     * @var string
     */
    protected $pass;
    /**
     * Grupo al que pertenecería el usuario $user.
     *
     * @var string
     */
    protected $group;
    /**
     * Unidad organizativa en la que están los usuarios.
     *
     * @var string
     */
    protected $userOrg = 'GRUPO (OU) DE LOS USUARIOS';
    /**
     * Host del servidor LDAP.
     *
     * @var string.
     */
    protected $host = 'HOST';
    /**
     * Puerto del servidor LDAP.
     *
     * @var string
     */
    protected $port = 'PUERTO';
    /**
     * DN Base. Por ejemplo: 'dc=uno, dc=dos'.
     *
     * @var string
     */
    protected $basedn = 'DN BASE';
    /**
     * Conexión con el servidor LDAP.
     *
     * @var resource|bool
     */
    protected $ldap = false;
    /**
     * Indica si se ha ligado con un usuario y contraseña o no.
     *
     * @var bool
     */
    protected $binded = false;


    /**
     * Constructor de nuestra clase. Se le puede pasar una configuración
     * opcional por si se quisiera modificar los valores de conexión definidos
     * en la clase.
     *
     * @param array $config Configuración para inicializar el objeto. Acepta:
     * 'host', 'port', 'basedn' y 'userOrg'.
     */
    public function __construct($config = [])
    {
        $host = false;
        $port = false;
        $basedn = false;
        $userOrg = false;
        extract($config, EXTR_IF_EXISTS);
        $this->setHost($host);
        $this->setPort($port);
        $this->setUserOrg($userOrg);
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
    protected function permissions()
    {
        return [

        ];
    }

    /**
     * Establece un valor para la propiedad host.
     *
     * @param string|false $val El nuevo valor para host. Si es false no se hará
     * el cambio.
     */
    protected function setHost($val)
    {
        $this->assign($val, 'host');
    }

    /**
     * Establece un valor para la propiedad port.
     *
     * @param string|false $val El nuevo valor para port. Si es false no se hará
     * el cambio.
     */
    protected function setPort($val)
    {
        $this->assign($val, 'port');
    }

    /**
     * Establece un valor para la propiedad basedn.
     *
     * @param string|bool $val El nuevo valor para basedn. Si es false no se
     * hará el cambio.
     */
    protected function setBasedn($val)
    {
        $this->assign($val, 'host');
    }

    /**
     * Establece un valor para la propiedad group.
     *
     * @param string|bool $val El nuevo valor para group. Si es false no se
     * hará el cambio.
     */
    public function setGroup($val)
    {
        $this->assign($val, 'group');
    }

    /**
     * Establece un valor para la propiedad userOrg.
     *
     * @param string|bool $val El nuevo valor para userOrg. Si es false no se
     * hará el cambio.
     */
    public function setUserOrg($val)
    {
        $this->assign($val, 'userOrg');
    }

    /**
     * Asigna un valor a una propiedad si dicho valor no es falso.
     *
     * @param  string|false $val  Valor que se asigna.
     * @param  string       $name Nombre de la variable.
     */
    protected function assign($val, $name)
    {
        if ($val) {
            $this->$name = $this->sanitize($val);
        }
    }

    /**
     * Devuelve el valor de la propiedad group.
     *
     * @return string Grupo al que pertenece.
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Devuelve el valor de la propiedad user.
     *
     * @return string Nombre de usuario.
     */
    public function getUsername()
    {
        return $this->user;
    }

    /**
     * Hace la conexión a un servidor LDAP.
     *
     * @throws \Exception Si no se pudo hacer la conexión.
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
     *
     * @return bool
     *
     * @throws \Exception Si no se pudo hacer la conexión.
     */
    public function bind($user, $pass)
    {
        if (!$this->ldap) {
            $this->conectar();
        }
        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
        if ($this->ldap) {
            $bind = @ldap_bind($this->ldap, "cn={$user},ou={$this->userOrg},{$this->basedn}", $pass);
            if ($bind) {
                $this->user = $user;
                $this->pass = $pass;
                $this->binded = true;
                return true;
            }
        }
        throw new \Exception('El nombre de usuario o la contraseña son incorrectos.', 1);
    }

    /**
     * Comprueba que el usuario está o no en un grupo.
     *
     * @param string $group Nombre del grupo que se comprueba que pertenece.
     * @param string $organization Nombre de la unidad organizativa (ou) a la
     * que pertenece el grupo.
     *
     * @return bool Falso si no está en el grupo (o no se pudo comprobar)
     * o verdadero si sí lo está.
     */
    public function checkGroup($group, $organization)
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
     *
     * @throws \Exception Si no pertenece a ningún grupo.
     */
    protected function getPermission()
    {
        foreach ($this->permissions() as $group => $org) {
            if ($this->checkGroup($group, $org)) {
                $this->group = $group;
                return true;
            }
        }
        throw new \Exception('No se pudo comprobar la pertenencia a ningún Grupo.', 1);
    }

    /**
     * Hace un login. El login se considera correcto si se obtuvo un permiso
     * (grupo).
     *
     * @param  string $username Nombre de usuario del servidor LDAP.
     * @param  string $password Contraseña del servidor LDAP.
     *
     * @return bool
     */
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
     * Sanitiza los datos recogidos del cliente que vayan a ser introducidos
     * en LDAP para prevenir inyección de código.
     *
     * @param  string $var Variable a escapar.
     *
     * @return string
     */
    protected function sanitize($var)
    {
        return ldap_escape($var, null, LDAP_ESCAPE_FILTER);
    }
}
