<?php
// app/controllers/TreinoController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Professor', 'Recepcao']);

$tituloPagina = "Treinos";
require __DIR__ . '/../views/treinos/index.php';
