<?php
// app/controllers/CrmController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';


// Apenas administradores podem gerenciar funcionários
verificarRole(['Admin', 'Recepcao']);

$baseUrl = '/Gymflow/app/controllers/CrmController.php';
$acao = $_GET['acao'] ?? 'listar';

$tituloPagina = "CRM";

/* LISTAR LEADS */
if ($acao === 'listar') {

    $operacao = 'listar';

    require __DIR__ . '/../models/Crm.php';

    /** @var array $leads */

    require __DIR__ . '/../views/crm/index.php';
}


$tituloPagina = "CRM";
