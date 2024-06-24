<?php

$admin = [
    [
        'text' => 'Logs',
        'url' => '/logs',
        'can' => 'admin',
    ],
];

$submenu3 = [
    [
        'text' => 'Evolução',
        'url' => '/notas/importar',
        'can'=>'GR_EVOLUCAO'
    ],
];

$submenu2 = [
    [
        'type' => 'header',
        'text' => 'Carga Didática',
        'can'=>'HEADER_CD'
    ],
    [
        'text' => 'Disciplinas',
        'url' => 'relatorios/cargadidatica/disciplinas',
        'can'=>'RPT_CD_DISCIPLINA'
    ],
    [
        'text' => 'Docente',
        'url' => 'relatorios/cargadidatica/docentes',
        'can'=>'RPT_CD_DOCENTE'
    ],
    [
        'type' => 'divider',
        'can'=>'DIV_CD_SCD'
    ],
    [
        'type' => 'header',
        'text' => 'Sem Carga Didática',
        'can'=>'HEADER_SCD'
    ],
    [
        'text' => 'Docente',
        'url' => 'relatorios/semcargadidatica/docentes',
        'can'=>'RPT_SCD_DOCENTES'
    ],
    [
        'type' => 'divider',
        'can'=>'DIV_CD_MONITORIA'
    ],
    [
        'type' => 'header',
        'text' => 'Análise de bolsas',
        'can'=>'HEADER_MONITORIA'
    ],
    [
        'text' => 'Monitoria',
        'url' => 'relatorios/analisedebolsas/monitoria',
        'can'=>'RPT_MONITORIA'
    ],
    [
        'type' => 'divider',
        'can'=>'DIV_MONITORIA_DIS'
    ],
    [
        'type' => 'header',
        'text' => 'Discentes',
        'can'=>'HEADER_DIS'
    ],
    [
        'text' => 'Ingressantes',
        'url' => 'relatorios/discentes/ingressantes',
        'can'=>'RPT_DIS_ING'
    ],
    [
        'text' => 'Estabilidade',
        'url' => 'relatorios/discentes/estabilidade',
        'can'=>'RPT_DIS_EST'
    ],
];

$menu = [
    [
        # este item de menu será substituido no momento da renderização
        'key' => 'menu_dinamico',
    ],
    [
        'text' => 'Relatórios',
        'submenu' => $submenu2,
        'can' => 'user',
    ],
    [
        'text' => 'Graduação',
        'submenu' => $submenu3,
        'can' => 'user',
    ],
    [
        'text' => 'Administrador',
        'submenu' => $admin,
        'can' => 'admin',
    ],
];

$right_menu = [
    [
        // menu utilizado para views da biblioteca senhaunica-socialite.
        'key' => 'senhaunica-socialite',
    ],
    [
        'key' => 'laravel-tools',
    ],
];

return [
    # valor default para a tag title, dentro da section title.
    # valor pode ser substituido pela aplicação.
    'title' => config('app.name'),

    # USP_THEME_SKIN deve ser colocado no .env da aplicação
    'skin' => env('USP_THEME_SKIN', 'uspdev'),

    # chave da sessão. Troque em caso de colisão com outra variável de sessão.
    'session_key' => 'laravel-usp-theme',

    # usado na tag base, permite usar caminhos relativos nos menus e demais elementos html
    # na versão 1 era dashboard_url
    'app_url' => config('app.url'),

    # login e logout
    'logout_method' => 'POST',
    'logout_url' => 'logout',
    'login_url' => 'login',

    # menus
    'menu' => $menu,
    'right_menu' => $right_menu,

    # mensagens flash - https://uspdev.github.io/laravel#31-mensagens-flash
    'mensagensFlash' => false,

    # container ou container-fluid
    'container' => 'container-fluid',

];
