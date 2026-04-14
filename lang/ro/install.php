<?php

return [

    'title'        => 'Instalare Contensio',
    'installation' => 'Instalare',

    'steps' => [
        'database' => 'Bază de date',
        'website'  => 'Website',
        'account'  => 'Cont',
        'done'     => 'Finalizat',
    ],

    'requirements' => [
        'title'            => 'Cerințe de sistem',
        'subtitle'         => 'Verificăm dacă serverul tău este pregătit pentru Contensio.',
        'failed_title'     => 'Unele cerințe nu sunt îndeplinite.',
        'failed_subtitle'  => 'Contactează furnizorul de hosting pentru a rezolva problemele de mai jos, apoi reîncarcă această pagină.',
        'php_version'      => 'Versiune PHP',
        'php_required'     => 'PHP :version sau mai nou',
        'extensions'       => 'Extensii PHP',
        'recommended'      => 'Recomandate',
        'permissions'      => 'Permisiuni fișiere',
        'disk_space'       => 'Spațiu pe disc',
        'disk_required'    => 'Minimum 100 MB liber',
        'disk_available'   => ':mb MB disponibili',
        'check_again'      => '↺ Verifică din nou',
        'fix_issues'       => 'Rezolvă problemele pentru a continua',
    ],

    'database' => [
        'title'                => 'Conexiune la baza de date',
        'subtitle'             => 'Aceste detalii sunt furnizate de furnizorul tău de hosting. Verifică panoul de control (cPanel, Plesk) sau emailul de bun venit.',
        'host'                 => 'Server bază de date',
        'name'                 => 'Nume bază de date',
        'username'             => 'Utilizator',
        'password'             => 'Parolă',
        'password_placeholder' => 'Lasă gol dacă nu există parolă',
        'advanced'             => 'Opțiuni avansate',
        'port'                 => 'Port',
        'test'                 => 'Testează conexiunea',
        'connecting'           => 'Se conectează...',
    ],

    'website' => [
        'title'                 => 'Website-ul tău',
        'subtitle'              => 'Informații de bază despre website-ul tău. Poți modifica oricând din panoul de administrare.',
        'site_name'             => 'Numele website-ului',
        'site_name_placeholder' => 'Website-ul meu',
        'language'              => 'Limba implicită',
        'language_hint'         => 'Limba principală a conținutului. Poți adăuga mai multe limbi ulterior.',
    ],

    'account' => [
        'title'                        => 'Contul tău de administrator',
        'subtitle'                     => 'Acesta este contul cu care vei gestiona website-ul. Păstrează parola în siguranță.',
        'name'                         => 'Numele tău',
        'name_placeholder'             => 'Ion Popescu',
        'email'                        => 'Adresă de email',
        'email_placeholder'            => 'tu@exemplu.com',
        'email_hint'                   => 'Aceasta va fi adresa de email pentru autentificare.',
        'password'                     => 'Parolă',
        'password_placeholder'         => 'Minimum 8 caractere',
        'confirm_password'             => 'Confirmă parola',
        'confirm_password_placeholder' => 'Repetă parola',
        'strength'                     => [
            'weak'   => 'Slabă',
            'fair'   => 'Acceptabilă',
            'good'   => 'Bună',
            'strong' => 'Puternică',
        ],
        'submit'     => 'Instalează Contensio',
        'installing' => 'Se instalează...',
    ],

    'complete' => [
        'title'       => 'Instalare finalizată!',
        'subtitle'    => 'Website-ul tău este gata. Salvează aceste detalii într-un loc sigur.',
        'website_url' => 'URL website',
        'admin_panel' => 'Panou de administrare',
        'go_to_admin' => 'Mergi la panoul de administrare',
        'note'        => 'Instalatorul este acum dezactivat. Pentru reinstalare, elimină :key din fișierul .env.',
    ],

    'buttons' => [
        'continue' => 'Continuă →',
        'back'     => 'Înapoi',
    ],

];
