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
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page {
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            background: #eee;
            padding: 5px 10px;
            border-left: 4px solid #333;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .info-item strong {
            display: block;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .info-item span {
            font-size: 14px;
            font-weight: bold;
        }
        .terms {
            font-size: 12px;
            text-align: justify;
            margin-bottom: 2rem;
        }
        .terms p {
            margin-bottom: 8px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            height: 30px;
            margin-bottom: 5px;
        }
        
        @page {
            size: A4;
            margin: 1.5cm;
        }

        @media print {
            body {
                background-color: #fff;
            }
            .page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
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
        
        <div style="text-align: center; margin-top: 3rem; font-size: 12px; color: #999;">
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
