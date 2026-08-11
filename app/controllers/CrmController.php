<?php
// app/controllers/CrmController.php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/sessao.php';

verificarRole(['Admin', 'Recepcao']);

$tituloPagina = "CRM";
require __DIR__ . '/../views/crm/index.php';
