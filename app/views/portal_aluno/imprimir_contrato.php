<?php
/** @var array $contrato */
if (!isset($contrato)) {
    die("Acesso negado.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato - <?= htmlspecialchars($contrato['nome_aluno']) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/imprimir-contrato.css">
</head>
<body>

    <div class="page">
        <div class="header">
            <h1>Termo de Adesão e Contrato de Prestação de Serviços</h1>
            <p><?= htmlspecialchars($contrato['nome_filial']) ?></p>
        </div>

        <div class="section-title">1. Identificação do Contratante (Aluno)</div>
        <div class="info-grid">
            <div class="info-item">
                <strong>Nome Completo</strong>
                <span><?= htmlspecialchars($contrato['nome_aluno']) ?></span>
            </div>
            <div class="info-item">
                <strong>CPF</strong>
                <span><?= htmlspecialchars($contrato['cpf_aluno']) ?></span>
            </div>
            <div class="info-item">
                <strong>Telefone</strong>
                <span><?= htmlspecialchars($contrato['tel_aluno']) ?></span>
            </div>
        </div>

        <div class="section-title">2. Identificação da Contratada (Academia)</div>
        <div class="info-grid">
            <div class="info-item">
                <strong>Razão Social</strong>
                <span><?= htmlspecialchars($contrato['nome_filial']) ?></span>
            </div>
            <div class="info-item">
                <strong>CNPJ</strong>
                <span><?= htmlspecialchars($contrato['cnpj_filial'] ?? '00.000.000/0001-00') ?></span>
            </div>
            <div class="info-item">
                <strong>Endereço</strong>
                <span><?= htmlspecialchars($contrato['end_filial'] ?? 'Endereço não cadastrado') ?></span>
            </div>
        </div>

        <div class="section-title">3. Dados do Plano Escolhido</div>
        <div class="info-grid">
            <div class="info-item">
                <strong>Plano</strong>
                <span><?= htmlspecialchars($contrato['nome_plano']) ?></span>
            </div>
            <div class="info-item">
                <strong>Categoria</strong>
                <span><?= htmlspecialchars($contrato['categoria']) ?></span>
            </div>
            <div class="info-item">
                <strong>Data de Início</strong>
                <span><?= date('d/m/Y', strtotime($contrato['inicio'])) ?></span>
            </div>
            <div class="info-item">
                <strong>Data de Término</strong>
                <span><?= date('d/m/Y', strtotime($contrato['fim'])) ?></span>
            </div>
            <div class="info-item">
                <strong>Valor</strong>
                <span>R$ <?= number_format($contrato['valor'], 2, ',', '.') ?></span>
            </div>
            <div class="info-item">
                <strong>Desconto Aplicado</strong>
                <span>R$ <?= number_format($contrato['desconto'], 2, ',', '.') ?></span>
            </div>
        </div>

        <div class="section-title">4. Cláusulas e Condições Gerais</div>
        <div class="terms">
            <p><strong>Cláusula 1:</strong> O CONTRATANTE terá direito a utilizar as dependências da CONTRATADA durante o horário de funcionamento, respeitando as regras internas e os limites do plano escolhido.</p>
            <p><strong>Cláusula 2:</strong> É obrigatória a apresentação de atestado médico confirmando que o CONTRATANTE está apto à prática de atividades físicas, isentando a CONTRATADA de responsabilidades sobre lesões preexistentes.</p>
            <p><strong>Cláusula 3:</strong> Em caso de atraso no pagamento da mensalidade, será cobrada multa de 2% sobre o valor da parcela, acrescida de juros de mora de 1% ao mês.</p>
            <p><strong>Cláusula 4:</strong> O presente contrato poderá ser rescindido por qualquer uma das partes, mediante aviso prévio de 30 dias. Em caso de planos com fidelidade, será cobrada multa de 20% sobre o saldo remanescente.</p>
            <p><strong>Cláusula 5:</strong> O acesso às instalações é pessoal e intransferível, sendo estritamente proibido o compartilhamento de senhas, biometria ou cartões de acesso.</p>
        </div>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong><?= htmlspecialchars($contrato['nome_aluno']) ?></strong><br>
                <span>Contratante (Aluno)</span>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong><?= htmlspecialchars($contrato['nome_filial']) ?></strong><br>
                <span>Contratada (Academia)</span>
            </div>
        </div>
        
        <div class="contrato-rodape-eletronico">
            Documento gerado eletronicamente em <?= date('d/m/Y \à\s H:i:s') ?> pelo sistema Gymflow.
        </div>
    </div>

    <script>
        // Imprime automaticamente a página e depois foca de volta
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
