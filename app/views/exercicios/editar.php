<?php
// ========================================
// GYMFLOW - EDITAR EXERCÍCIO
// app/views/exercicios/editar.php
// ========================================

if (!isset($exercicio) || !is_array($exercicio)) {

    header(
        'Location: ' . BASE_URL .
            '/app/controllers/ExercicioController.php'
    );

    exit;
}

$modoEdicao = true;

require __DIR__ . '/formulario.php';
