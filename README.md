# LDAP

Clase para manejar un servidor LDAP + PHP.

Ver la [Api](http://mavalroot.github.io/auth_ldap/).

## Estructura

La clase LDAP está pensada para manejar un Active Directory que tenga la siguiente estructura:

- OU=GRUPOPERSONAS
    - DN=NOMBRE1
        - memberOf=ou=GRUPOPERMISOS,dn=PERMISO1
    - DN=NOMBRE2
        - memberOf=ou=GRUPOPERMISOS,dn=PERMISO2
- OU=GRUPOPERMISOS
    - DN=PERMISO1
        - member=ou=GRUPOPERSONAS,dn=NOMBRE1
    - DN=PERMISO1
        - member=ou=GRUPOPERSONAS,dn=NOMBRE2

Si se necesita una estructura diferente deberá modificarse.

## Configuración

Para configurar la clase LDAP se deberá hacer lo siguiente:

#### 1. Cambiar las propiedades de conexión

```PHP
protected $userOrg = 'GRUPO (OU) DE LOS USUARIOS';
protected $host = 'HOST';
protected $port = 'PUERTO';
protected $basedn = 'DN BASE';
```

#### 2. Definir los permisos que se comprobarán

```PHP
protected function permissions()
{
    return [
        'grupo1' => 'ou1',
        'grupo2' => 'ou1',
        'grupo3' => 'ou2',
    ];
}
```

*No soporta multi-permisos.*
