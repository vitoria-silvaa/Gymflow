<?php
// app/views/shared/portfolio_footer.php

$nomeMarca = trim($config['app_name'] ?? 'GymFlow');
$logoMarca = trim($config['logo_url'] ?? '');
$emailRodape = trim($config['contact_email'] ?? '');
$telefoneRodape = trim($config['contact_phone'] ?? '');
$instagramRodape = trim($config['instagram_url'] ?? '');
$facebookRodape = trim($config['facebook_url'] ?? '');
$tiktokRodape = trim($config['tiktok_url'] ?? '');
$whatsappRodape = trim($config['whatsapp_url'] ?? '');
?>

<footer class="portfolio-footer">
    <div class="footer-linha-topo"></div>

    <div class="footer-conteudo">
        <div class="footer-marca">
            <?php if ($logoMarca !== ''): ?>
                <a href="#inicio" class="footer-logo-link" aria-label="Voltar ao início">
                    <img
                        src="<?= htmlspecialchars($logoMarca) ?>"
                        alt=""
                        class="footer-logo"
                        onerror="this.style.display='none';"
                    >
                </a>
            <?php endif; ?>
        </div>

        <div class="footer-grid">
            <div class="footer-coluna">
                <h3>Navegue</h3>

                <nav class="footer-links" aria-label="Navegação do rodapé">
                    <a href="#inicio">Home</a>
                    <a href="#unidades">Unidades</a>
                    <a href="#modalidades">Modalidades</a>
                    <a href="#sobre">Sobre nós</a>
                    <a href="#contato">Contato</a>
                </nav>
            </div>

            <div class="footer-coluna">
                <h3>Contato</h3>

                <div class="footer-contatos">
                    <?php if ($telefoneRodape !== ''): ?>
                        <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $telefoneRodape)) ?>">
                            <span aria-hidden="true">☎</span>
                            <?= htmlspecialchars($telefoneRodape) ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($emailRodape !== ''): ?>
                        <a href="mailto:<?= htmlspecialchars($emailRodape) ?>">
                            <span aria-hidden="true">✉</span>
                            <?= htmlspecialchars($emailRodape) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer-coluna">
                <h3>Siga-nos</h3>

                <div class="footer-redes">
                    <?php if ($instagramRodape !== ''): ?>
                        <a
                            href="<?= htmlspecialchars($instagramRodape) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Instagram"
                        >
                            <span aria-hidden="true">◎</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($facebookRodape !== ''): ?>
                        <a
                            href="<?= htmlspecialchars($facebookRodape) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Facebook"
                        >
                            <span aria-hidden="true">f</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($tiktokRodape !== ''): ?>
                        <a
                            href="<?= htmlspecialchars($tiktokRodape) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="TikTok"
                        >
                            <span aria-hidden="true">♪</span>
                        </a>
                    <?php endif; ?>

                    <?php if (
                        $instagramRodape === ''
                        && $facebookRodape === ''
                        && $tiktokRodape === ''
                    ): ?>
                        <span class="footer-sem-redes">Redes sociais em breve.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="footer-base">
            <p>
                © <?= date('Y') ?> <?= htmlspecialchars($nomeMarca) ?>.
                Todos os direitos reservados.
            </p>

            <a href="#inicio">Voltar ao topo ↑</a>
        </div>
    </div>

    <?php if ($whatsappRodape !== ''): ?>
        <a
            href="<?= htmlspecialchars($whatsappRodape) ?>"
            class="footer-whatsapp"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Falar pelo WhatsApp"
            title="Falar pelo WhatsApp"
        >
            <span aria-hidden="true">☎</span>
        </a>
    <?php endif; ?>
</footer>

</body>
</html>
