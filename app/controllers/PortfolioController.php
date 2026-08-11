<?php
// app/controllers/PortfolioController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

// Apenas administradores configuram o portfolio
verificarRole(['Admin']);

$tituloPagina = "Portfólio";
require __DIR__ . '/../views/portfolio/index.php';
