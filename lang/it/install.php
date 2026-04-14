<?php

return [

    'title'        => 'Installa Contensio',
    'installation' => 'Installazione',

    'steps' => [
        'database' => 'Database',
        'website'  => 'Sito web',
        'account'  => 'Account',
        'done'     => 'Completato',
    ],

    'requirements' => [
        'title'            => 'Requisiti di sistema',
        'subtitle'         => 'Verifica se il tuo server è pronto per Contensio.',
        'failed_title'     => 'Alcuni requisiti non sono soddisfatti.',
        'failed_subtitle'  => 'Contatta il tuo provider di hosting per risolvere i problemi seguenti, poi aggiorna questa pagina.',
        'php_version'      => 'Versione PHP',
        'php_required'     => 'PHP :version o superiore',
        'extensions'       => 'Estensioni PHP',
        'recommended'      => 'Consigliate',
        'permissions'      => 'Permessi file',
        'disk_space'       => 'Spazio su disco',
        'disk_required'    => 'Almeno 100 MB liberi',
        'disk_available'   => ':mb MB disponibili',
        'check_again'      => '↺ Controlla di nuovo',
        'fix_issues'       => 'Risolvi i problemi per continuare',
    ],

    'database' => [
        'title'                => 'Connessione al database',
        'subtitle'             => 'Questi dati sono forniti dal tuo provider di hosting. Controlla il tuo pannello di controllo (cPanel, Plesk) o l\'email di benvenuto.',
        'host'                 => 'Server database',
        'name'                 => 'Nome database',
        'username'             => 'Nome utente',
        'password'             => 'Password',
        'password_placeholder' => 'Lascia vuoto se non c\'è password',
        'advanced'             => 'Opzioni avanzate',
        'port'                 => 'Porta',
        'test'                 => 'Testa connessione',
        'connecting'           => 'Connessione in corso...',
    ],

    'website' => [
        'title'                 => 'Il tuo sito web',
        'subtitle'              => 'Informazioni di base sul tuo sito. Puoi modificare tutto in seguito dal pannello di amministrazione.',
        'site_name'             => 'Nome del sito',
        'site_name_placeholder' => 'Il mio sito',
        'language'              => 'Lingua predefinita',
        'language_hint'         => 'La lingua principale dei contenuti. È possibile aggiungere altre lingue in seguito.',
    ],

    'account' => [
        'title'                        => 'Il tuo account amministratore',
        'subtitle'                     => 'Questo è il tuo accesso per gestire il sito web. Conserva la password al sicuro.',
        'name'                         => 'Il tuo nome',
        'name_placeholder'             => 'Mario Rossi',
        'email'                        => 'Indirizzo email',
        'email_placeholder'            => 'tu@esempio.it',
        'email_hint'                   => 'Questo sarà il tuo indirizzo email di accesso.',
        'password'                     => 'Password',
        'password_placeholder'         => 'Almeno 8 caratteri',
        'confirm_password'             => 'Conferma password',
        'confirm_password_placeholder' => 'Ripeti la password',
        'strength'                     => [
            'weak'   => 'Debole',
            'fair'   => 'Discreta',
            'good'   => 'Buona',
            'strong' => 'Forte',
        ],
        'submit'     => 'Installa Contensio',
        'installing' => 'Installazione in corso...',
    ],

    'complete' => [
        'title'       => 'Installazione completata!',
        'subtitle'    => 'Il tuo sito è pronto. Ecco i tuoi dettagli — salvali in un posto sicuro.',
        'website_url' => 'URL del sito',
        'admin_panel' => 'Pannello di amministrazione',
        'go_to_admin' => 'Vai al pannello di amministrazione',
        'note'        => 'Il programma di installazione è ora disabilitato. Per reinstallare, rimuovi :key dal tuo file .env.',
    ],

    'buttons' => [
        'continue' => 'Continua →',
        'back'     => 'Indietro',
    ],

];
