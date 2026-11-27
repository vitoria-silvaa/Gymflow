<?php
/**
 * View de e-mail: Recuperação de senha
 * Nota: Clientes de e-mail exigem estilos inline para renderização consistente.
 *
 * @var string $nomeAcademiaHtml
 * @var string $linkSeguro
 * @var string $corFundo
 * @var string $corCartao
 * @var string $corDestaque
 * @var string $corBotao
 * @var string $corTextoBotao
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de senha - <?= $nomeAcademiaHtml ?></title>
</head>
<body style="margin:0; padding:0; background-color:<?= $corFundo ?>; font-family:Arial,Helvetica,sans-serif; color:#f5f5f5;">

    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Recupere seu acesso à <?= $nomeAcademiaHtml ?> de forma segura.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="width:100%; background-color:<?= $corFundo ?>;">
        <tr>
            <td align="center" style="padding:36px 14px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    style="width:100%; max-width:600px; background-color:<?= $corCartao ?>; border:1px solid #303030; border-radius:14px;">

                    <tr>
                        <td style="height:5px; background-color:<?= $corDestaque ?>; font-size:0; line-height:0;">
                            &nbsp;
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:34px 24px 22px;">
                            <p style="margin:0; font-size:30px; font-weight:bold; letter-spacing:1px; color:#ffffff;">
                                <?= $nomeAcademiaHtml ?>
                            </p>
                            <p style="margin:10px 0 0; font-size:11px; letter-spacing:2px; color:#a3a3a3;">
                                RECUPERAÇÃO DE ACESSO
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 30px 34px;">

                            <h1 style="margin:0 0 18px; font-size:25px; line-height:1.3; color:#ffffff; text-align:center;">
                                Vamos recuperar seu acesso?
                            </h1>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.8; color:#d1d1d1;">
                                Olá!
                            </p>

                            <p style="margin:0; font-size:15px; line-height:1.8; color:#d1d1d1;">
                                Recebemos uma solicitação para redefinir sua senha na
                                <?= $nomeAcademiaHtml ?>. Clique no botão abaixo para criar
                                uma nova senha e voltar a acessar sua conta.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="18" style="height:18px; font-size:0; line-height:18px;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" bgcolor="<?= $corBotao ?>" style="border-radius:8px;">
                                        <a href="<?= $linkSeguro ?>"
                                            style="display:inline-block; padding:16px 28px; font-size:15px; font-weight:bold; color:<?= $corTextoBotao ?>; text-decoration:none; border-radius:8px;">
                                            REDEFINIR MINHA SENHA
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="18" style="height:18px; font-size:0; line-height:18px;">
                                        &nbsp;
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color:<?= $corCartao ?>; border:1px solid #353535; border-radius:8px;">
                                <tr>
                                    <td style="padding:16px;">
                                        <p style="margin:0; font-size:13px; line-height:1.8; color:#f0f0f0;">
                                            <strong style="color:<?= $corDestaque ?>;">ATENÇÃO</strong><br>
                                            Este link expira em 1 hora por segurança.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; font-size:13px; line-height:1.8; color:#a3a3a3;">
                                Se você não solicitou a recuperação de senha, basta ignorar este e-mail.
                                Sua senha atual não será alterada por esta mensagem.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td align="center"
                            style="padding:22px 20px; border-top:1px solid #303030; background-color:<?= $corFundo ?>; border-radius:0 0 14px 14px;">

                            <p style="margin:0 0 8px; font-size:13px; font-weight:bold; color:<?= $corDestaque ?>;">
                                <?= $nomeAcademiaHtml ?>
                            </p>

                            <p style="margin:0; font-size:11px; line-height:1.8; color:#888888;">
                                Este é um e-mail automático. Por favor, não responda.<br>
                                Sua segurança é importante para nós.
                            </p>

                        </td>
                    </tr>

                </table>

                <p style="margin:18px 0 0; font-size:11px; color:#777777; text-align:center;">
                    <?= $nomeAcademiaHtml ?> &copy; Todos os direitos reservados.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>
