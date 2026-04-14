<?php

return [

    'title'        => 'Instalar Contensio',
    'installation' => 'Instalación',

    'steps' => [
        'database' => 'Base de datos',
        'website'  => 'Sitio web',
        'account'  => 'Cuenta',
        'done'     => 'Hecho',
    ],

    'requirements' => [
        'title'            => 'Requisitos del sistema',
        'subtitle'         => 'Comprobando si su servidor está listo para Contensio.',
        'failed_title'     => 'Algunos requisitos no se cumplen.',
        'failed_subtitle'  => 'Contacte a su proveedor de hosting para resolver los problemas a continuación, luego recargue esta página.',
        'php_version'      => 'Versión de PHP',
        'php_required'     => 'PHP :version o superior',
        'extensions'       => 'Extensiones PHP',
        'recommended'      => 'Recomendadas',
        'permissions'      => 'Permisos de archivos',
        'disk_space'       => 'Espacio en disco',
        'disk_required'    => 'Al menos 100 MB libres',
        'disk_available'   => ':mb MB disponibles',
        'check_again'      => '↺ Comprobar de nuevo',
        'fix_issues'       => 'Resolver problemas para continuar',
    ],

    'database' => [
        'title'                => 'Conexión a la base de datos',
        'subtitle'             => 'Estos datos son proporcionados por su proveedor de hosting. Consulte su panel de control (cPanel, Plesk) o el correo de bienvenida.',
        'host'                 => 'Servidor de base de datos',
        'name'                 => 'Nombre de la base de datos',
        'username'             => 'Usuario',
        'password'             => 'Contraseña',
        'password_placeholder' => 'Dejar vacío si no hay contraseña',
        'advanced'             => 'Opciones avanzadas',
        'port'                 => 'Puerto',
        'test'                 => 'Probar conexión',
        'connecting'           => 'Conectando...',
    ],

    'website' => [
        'title'                 => 'Su sitio web',
        'subtitle'              => 'Información básica sobre su sitio web. Puede cambiar todo esto más tarde desde el panel de administración.',
        'site_name'             => 'Nombre del sitio',
        'site_name_placeholder' => 'Mi sitio web',
        'language'              => 'Idioma predeterminado',
        'language_hint'         => 'El idioma principal del contenido. Se pueden agregar más idiomas más adelante.',
    ],

    'account' => [
        'title'                        => 'Su cuenta de administrador',
        'subtitle'                     => 'Este es su acceso para gestionar el sitio web. Guarde su contraseña en un lugar seguro.',
        'name'                         => 'Su nombre',
        'name_placeholder'             => 'Juan García',
        'email'                        => 'Dirección de correo',
        'email_placeholder'            => 'usted@ejemplo.com',
        'email_hint'                   => 'Esta será su dirección de correo para iniciar sesión.',
        'password'                     => 'Contraseña',
        'password_placeholder'         => 'Al menos 8 caracteres',
        'confirm_password'             => 'Confirmar contraseña',
        'confirm_password_placeholder' => 'Repita su contraseña',
        'strength'                     => [
            'weak'   => 'Débil',
            'fair'   => 'Aceptable',
            'good'   => 'Buena',
            'strong' => 'Fuerte',
        ],
        'submit'     => 'Instalar Contensio',
        'installing' => 'Instalando...',
    ],

    'complete' => [
        'title'       => '¡Instalación completada!',
        'subtitle'    => 'Su sitio web está listo. Aquí están sus datos — guárdelos en un lugar seguro.',
        'website_url' => 'URL del sitio web',
        'admin_panel' => 'Panel de administración',
        'go_to_admin' => 'Ir al panel de administración',
        'note'        => 'El instalador está ahora desactivado. Para reinstalar, elimine :key de su archivo .env.',
    ],

    'buttons' => [
        'continue' => 'Continuar →',
        'back'     => 'Atrás',
    ],

];
