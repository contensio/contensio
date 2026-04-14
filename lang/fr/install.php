<?php

return [

    'title'        => 'Installer Contensio',
    'installation' => 'Installation',

    'steps' => [
        'database' => 'Base de données',
        'website'  => 'Site web',
        'account'  => 'Compte',
        'done'     => 'Terminé',
    ],

    'requirements' => [
        'title'            => 'Prérequis système',
        'subtitle'         => 'Vérification si votre serveur est prêt pour Contensio.',
        'failed_title'     => 'Certains prérequis ne sont pas satisfaits.',
        'failed_subtitle'  => 'Contactez votre hébergeur pour résoudre les problèmes ci-dessous, puis actualisez cette page.',
        'php_version'      => 'Version PHP',
        'php_required'     => 'PHP :version ou supérieur',
        'extensions'       => 'Extensions PHP',
        'recommended'      => 'Recommandées',
        'permissions'      => 'Permissions des fichiers',
        'disk_space'       => 'Espace disque',
        'disk_required'    => 'Au moins 100 Mo libres',
        'disk_available'   => ':mb Mo disponibles',
        'check_again'      => '↺ Vérifier à nouveau',
        'fix_issues'       => 'Résoudre les problèmes pour continuer',
    ],

    'database' => [
        'title'                => 'Connexion à la base de données',
        'subtitle'             => 'Ces informations sont fournies par votre hébergeur. Consultez votre panneau de contrôle (cPanel, Plesk) ou l\'email de bienvenue.',
        'host'                 => 'Hôte de la base de données',
        'name'                 => 'Nom de la base de données',
        'username'             => 'Nom d\'utilisateur',
        'password'             => 'Mot de passe',
        'password_placeholder' => 'Laisser vide si aucun mot de passe',
        'advanced'             => 'Options avancées',
        'port'                 => 'Port',
        'test'                 => 'Tester la connexion',
        'connecting'           => 'Connexion en cours...',
    ],

    'website' => [
        'title'                 => 'Votre site web',
        'subtitle'              => 'Informations de base sur votre site. Vous pouvez tout modifier ultérieurement depuis le panneau d\'administration.',
        'site_name'             => 'Nom du site',
        'site_name_placeholder' => 'Mon site web',
        'language'              => 'Langue par défaut',
        'language_hint'         => 'La langue principale du contenu. D\'autres langues peuvent être ajoutées plus tard.',
    ],

    'account' => [
        'title'                        => 'Votre compte administrateur',
        'subtitle'                     => 'C\'est votre connexion pour gérer le site. Gardez votre mot de passe en sécurité.',
        'name'                         => 'Votre nom',
        'name_placeholder'             => 'Jean Dupont',
        'email'                        => 'Adresse email',
        'email_placeholder'            => 'vous@exemple.com',
        'email_hint'                   => 'Ce sera votre adresse email de connexion.',
        'password'                     => 'Mot de passe',
        'password_placeholder'         => 'Au moins 8 caractères',
        'confirm_password'             => 'Confirmer le mot de passe',
        'confirm_password_placeholder' => 'Répétez votre mot de passe',
        'strength'                     => [
            'weak'   => 'Faible',
            'fair'   => 'Acceptable',
            'good'   => 'Bon',
            'strong' => 'Fort',
        ],
        'submit'     => 'Installer Contensio',
        'installing' => 'Installation en cours...',
    ],

    'complete' => [
        'title'       => 'Installation terminée !',
        'subtitle'    => 'Votre site est prêt. Voici vos informations — sauvegardez-les en lieu sûr.',
        'website_url' => 'URL du site',
        'admin_panel' => 'Panneau d\'administration',
        'go_to_admin' => 'Accéder au panneau d\'administration',
        'note'        => 'L\'installateur est maintenant désactivé. Pour réinstaller, supprimez :key de votre fichier .env.',
    ],

    'buttons' => [
        'continue' => 'Continuer →',
        'back'     => 'Retour',
    ],

];
