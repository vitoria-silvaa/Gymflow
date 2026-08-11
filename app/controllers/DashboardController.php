<?php
// app/controllers/DashboardController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

// Apenas funcionários autorizados acessam o painel administrativo
verificarRole(['Admin', 'Professor', 'Recepcao']);

$tituloPagina = "Dashboard";
require __DIR__ . '/../views/dashboard/index.php';
