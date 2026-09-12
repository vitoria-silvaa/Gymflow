<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? "GymCore"; ?></title>

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <link rel="stylesheet" href="/Gymflow/assets/css/css/global.css">
    <link rel="stylesheet" href="/Gymflow/assets/css/css/portifolio.css?v=6">

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        defer
    ></script>

    <style>
        :root {
            --portfolio-primary: <?= htmlspecialchars($config['primary_color'] ?? '#C9A227') ?>;
            --portfolio-secondary: <?= htmlspecialchars($config['secondary_color'] ?? '#000000') ?>;
        }
        .unidades-acao {
            margin: 22px 0 18px;
            text-align: center;
        }
        .unidades-texto-geral {
            color: var(--portfolio-primary);
            font-size: 14px;
            font-weight: 600;
        }
        .unidades-mapa {
            width: 100%;
            max-width: 1080px;
            margin: 0 auto;
            border: 1px solid rgba(255,255,255,.72);
            border-radius: 24px;
            overflow: hidden;
            background: #050505;
        }
        .unidades-mapa-conteudo {
            display: grid;
            grid-template-columns: minmax(250px, .72fr) minmax(0, 1.75fr);
            min-height: 230px;
        }
        .unidades-mapa-texto {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 30px 30px 30px 34px;
            background: #050505;
        }
        .unidades-mapa-texto h3 {
            margin: 0 0 10px;
            color: var(--portfolio-primary);
            font-size: 16px;
            line-height: 1.25;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .unidades-mapa-texto p {
            max-width: 230px;
            margin: 0 0 20px;
            color: #9d9d9d;
            font-size: 12px;
            line-height: 1.5;
        }
        .unidades-mapa-btn {
            width: fit-content;
            min-width: 165px;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 16px;
            color: #ffffff;
            background: transparent;
            border: 1px solid rgba(255,255,255,.82);
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            text-transform: uppercase;
            transition: .25s ease;
        }
        .unidades-mapa-btn:hover {
            color: #000000;
            background: var(--portfolio-primary);
            border-color: var(--portfolio-primary);
        }
        .unidades-mapa-btn-icone {
            color: var(--portfolio-primary);
            font-size: 15px;
            line-height: 1;
        }
        .unidades-mapa-btn:hover .unidades-mapa-btn-icone {
            color: #000000;
        }
        #mapa-unidades {
            width: 100%;
            height: 230px;
            min-height: 230px;
            background: #050505;
        }
        #mapa-unidades .leaflet-tile-pane {
            filter: grayscale(1) invert(1) sepia(.18) saturate(.75) brightness(.38) contrast(1.42);
        }
        #mapa-unidades .leaflet-control-attribution {
            padding: 1px 5px;
            color: #8f8f8f;
            background: rgba(0,0,0,.72);
            font-size: 8px;
        }
        #mapa-unidades .leaflet-control-attribution a {
            color: #b7b7b7;
        }
        #mapa-unidades .leaflet-popup-content-wrapper,
        #mapa-unidades .leaflet-popup-tip {
            background: #111111;
            color: #ffffff;
        }
        #mapa-unidades .leaflet-popup-content strong {
            display: block;
            margin-bottom: 6px;
            color: var(--portfolio-primary);
        }
        .gymflow-marker-wrap {
            background: transparent;
            border: 0;
        }
        .gymflow-pin {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: #090909;
            border: 2px solid var(--pin-color);
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            box-shadow: 0 0 0 3px rgba(0,0,0,.45);
        }
        .gymflow-pin span {
            color: var(--pin-color);
            font-size: 8px;
            font-weight: 800;
            transform: rotate(45deg);
        }
        @media (max-width: 760px) {
            .unidades-mapa {
                border-radius: 18px;
            }
            .unidades-mapa-conteudo {
                grid-template-columns: 1fr;
            }
            .unidades-mapa-texto {
                padding: 24px;
            }
            .unidades-mapa-texto p {
                max-width: 100%;
            }
            #mapa-unidades {
                height: 240px;
                min-height: 240px;
            }
        }
    </style>
</head>

<body>

<header class="portfolio-header">

    <div class="portfolio-logo">
        <h1><?= htmlspecialchars($tituloPagina ?? "GymCore") ?></h1>
    </div>

    <nav class="portfolio-nav">
        <ul>
            <li><a href="/Gymflow/index.php#inicio">Início</a></li>
            <li><a href="/Gymflow/index.php#planos">Planos</a></li>
            <li><a href="/Gymflow/index.php#unidades">Unidades</a></li>
            <li><a href="/Gymflow/index.php#modalidades">Modalidades</a></li>
            <li><a href="/Gymflow/index.php#sobre">Sobre Nós</a></li>
            <li><a href="/Gymflow/index.php#contato">Contato</a></li>
        </ul>
    </nav>

    <div class="portfolio-acoes">
        <a href="/Gymflow/app/controllers/LoginController.php?acao=login">Entrar</a>
        <a href="/Gymflow/app/controllers/MatriculaController.php">Matricule-se</a>
    </div>

</header>
