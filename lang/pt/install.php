<?php

return [

    'title'        => 'Instalar Contensio',
    'installation' => 'Instalação',

    'steps' => [
        'database' => 'Base de dados',
        'website'  => 'Website',
        'account'  => 'Conta',
        'done'     => 'Concluído',
    ],

    'requirements' => [
        'title'            => 'Requisitos do sistema',
        'subtitle'         => 'A verificar se o seu servidor está pronto para o Contensio.',
        'failed_title'     => 'Alguns requisitos não foram cumpridos.',
        'failed_subtitle'  => 'Contacte o seu fornecedor de alojamento para resolver os problemas abaixo e atualize esta página.',
        'php_version'      => 'Versão do PHP',
        'php_required'     => 'PHP :version ou superior',
        'extensions'       => 'Extensões PHP',
        'recommended'      => 'Recomendadas',
        'permissions'      => 'Permissões de ficheiros',
        'disk_space'       => 'Espaço em disco',
        'disk_required'    => 'Pelo menos 100 MB livres',
        'disk_available'   => ':mb MB disponíveis',
        'check_again'      => '↺ Verificar novamente',
        'fix_issues'       => 'Resolver problemas para continuar',
    ],

    'database' => [
        'title'                => 'Ligação à base de dados',
        'subtitle'             => 'Estes dados são fornecidos pelo seu fornecedor de alojamento. Consulte o seu painel de controlo (cPanel, Plesk) ou o email de boas-vindas.',
        'host'                 => 'Servidor da base de dados',
        'name'                 => 'Nome da base de dados',
        'username'             => 'Nome de utilizador',
        'password'             => 'Palavra-passe',
        'password_placeholder' => 'Deixe em branco se não houver palavra-passe',
        'advanced'             => 'Opções avançadas',
        'port'                 => 'Porta',
        'test'                 => 'Testar ligação',
        'connecting'           => 'A ligar...',
    ],

    'website' => [
        'title'                 => 'O seu website',
        'subtitle'              => 'Informações básicas sobre o seu website. Pode alterar tudo isto mais tarde no painel de administração.',
        'site_name'             => 'Nome do website',
        'site_name_placeholder' => 'O meu website',
        'language'              => 'Idioma predefinido',
        'language_hint'         => 'O idioma principal do conteúdo. Podem ser adicionados mais idiomas posteriormente.',
    ],

    'account' => [
        'title'                        => 'A sua conta de administrador',
        'subtitle'                     => 'Este é o seu acesso para gerir o website. Guarde a sua palavra-passe em segurança.',
        'name'                         => 'O seu nome',
        'name_placeholder'             => 'João Silva',
        'email'                        => 'Endereço de email',
        'email_placeholder'            => 'voce@exemplo.pt',
        'email_hint'                   => 'Este será o seu endereço de email de acesso.',
        'password'                     => 'Palavra-passe',
        'password_placeholder'         => 'Pelo menos 8 caracteres',
        'confirm_password'             => 'Confirmar palavra-passe',
        'confirm_password_placeholder' => 'Repita a palavra-passe',
        'strength'                     => [
            'weak'   => 'Fraca',
            'fair'   => 'Razoável',
            'good'   => 'Boa',
            'strong' => 'Forte',
        ],
        'submit'     => 'Instalar Contensio',
        'installing' => 'A instalar...',
    ],

    'complete' => [
        'title'       => 'Instalação concluída!',
        'subtitle'    => 'O seu website está pronto. Aqui estão os seus dados — guarde-os num local seguro.',
        'website_url' => 'URL do website',
        'admin_panel' => 'Painel de administração',
        'go_to_admin' => 'Ir para o painel de administração',
        'note'        => 'O instalador está agora desativado. Para reinstalar, remova :key do seu ficheiro .env.',
    ],

    'buttons' => [
        'continue' => 'Continuar →',
        'back'     => 'Voltar',
    ],

];
