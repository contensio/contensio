<?php

return [

    'title'        => 'Contensio installieren',
    'installation' => 'Installation',

    'steps' => [
        'database' => 'Datenbank',
        'website'  => 'Website',
        'account'  => 'Konto',
        'done'     => 'Fertig',
    ],

    'requirements' => [
        'title'            => 'Systemanforderungen',
        'subtitle'         => 'Überprüfung, ob Ihr Server für Contensio bereit ist.',
        'failed_title'     => 'Einige Anforderungen sind nicht erfüllt.',
        'failed_subtitle'  => 'Wenden Sie sich an Ihren Hosting-Anbieter, um die folgenden Probleme zu beheben, und aktualisieren Sie dann diese Seite.',
        'php_version'      => 'PHP-Version',
        'php_required'     => 'PHP :version oder höher',
        'extensions'       => 'PHP-Erweiterungen',
        'recommended'      => 'Empfohlen',
        'permissions'      => 'Dateiberechtigungen',
        'disk_space'       => 'Speicherplatz',
        'disk_required'    => 'Mindestens 100 MB frei',
        'disk_available'   => ':mb MB verfügbar',
        'check_again'      => '↺ Erneut prüfen',
        'fix_issues'       => 'Probleme beheben, um fortzufahren',
    ],

    'database' => [
        'title'                => 'Datenbankverbindung',
        'subtitle'             => 'Diese Daten werden von Ihrem Hosting-Anbieter bereitgestellt. Prüfen Sie Ihr Hosting-Panel (cPanel, Plesk) oder die Willkommens-E-Mail.',
        'host'                 => 'Datenbankserver',
        'name'                 => 'Datenbankname',
        'username'             => 'Benutzername',
        'password'             => 'Passwort',
        'password_placeholder' => 'Leer lassen, wenn kein Passwort',
        'advanced'             => 'Erweiterte Optionen',
        'port'                 => 'Port',
        'test'                 => 'Verbindung testen',
        'connecting'           => 'Verbinde...',
    ],

    'website' => [
        'title'                 => 'Ihre Website',
        'subtitle'              => 'Grundlegende Informationen über Ihre Website. Sie können alles später im Admin-Panel ändern.',
        'site_name'             => 'Website-Name',
        'site_name_placeholder' => 'Meine Website',
        'language'              => 'Standardsprache',
        'language_hint'         => 'Die Hauptsprache Ihres Website-Inhalts. Weitere Sprachen können später hinzugefügt werden.',
    ],

    'account' => [
        'title'                        => 'Ihr Administratorkonto',
        'subtitle'                     => 'Dies ist Ihre Anmeldung zur Verwaltung der Website. Bewahren Sie Ihr Passwort sicher auf.',
        'name'                         => 'Ihr Name',
        'name_placeholder'             => 'Max Mustermann',
        'email'                        => 'E-Mail-Adresse',
        'email_placeholder'            => 'sie@beispiel.de',
        'email_hint'                   => 'Dies wird Ihre Anmelde-E-Mail-Adresse sein.',
        'password'                     => 'Passwort',
        'password_placeholder'         => 'Mindestens 8 Zeichen',
        'confirm_password'             => 'Passwort bestätigen',
        'confirm_password_placeholder' => 'Passwort wiederholen',
        'strength'                     => [
            'weak'   => 'Schwach',
            'fair'   => 'Mäßig',
            'good'   => 'Gut',
            'strong' => 'Stark',
        ],
        'submit'     => 'Contensio installieren',
        'installing' => 'Wird installiert...',
    ],

    'complete' => [
        'title'       => 'Installation abgeschlossen!',
        'subtitle'    => 'Ihre Website ist bereit. Hier sind Ihre Daten — bewahren Sie diese sicher auf.',
        'website_url' => 'Website-URL',
        'admin_panel' => 'Admin-Panel',
        'go_to_admin' => 'Zum Admin-Panel',
        'note'        => 'Das Installationsprogramm ist jetzt deaktiviert. Zum Neuinstallieren entfernen Sie :key aus Ihrer .env-Datei.',
    ],

    'buttons' => [
        'continue' => 'Weiter →',
        'back'     => 'Zurück',
    ],

];
