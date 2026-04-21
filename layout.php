<?php

function getSiteNavigation(): array
{
    return [
        'home' => ['label' => 'Accueil', 'href' => 'index.php'],
        'shop' => ['label' => 'E-shop', 'href' => 'produits.php'],
        'about' => ['label' => 'About', 'href' => 'about.php'],
        'contact' => ['label' => 'Contact', 'href' => 'contact.php'],
    ];
}

function renderSiteHeader(string $currentPage, int $cartCount = 0): void
{
    $items = getSiteNavigation();
    ?>
    <header class="site-header">
        <div class="site-header-inner">
            <a href="index.php" class="site-brand" aria-label="Bruxelles Notes, retour a l accueil">
                <span class="site-brand-name">Bruxelles Notes</span>
                <span class="site-brand-tag">Maison de parfum</span>
            </a>

            <nav class="site-nav" aria-label="Navigation principale">
                <?php foreach ($items as $key => $item): ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                       class="site-nav-link"
                       <?= $currentPage === $key ? 'aria-current="page"' : '' ?>>
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="site-header-actions">
                <a href="basket.php" title="Mon panier" class="nav-panier" <?= $currentPage === 'basket' ? 'aria-current="page"' : '' ?>>
                    <span class="nav-panier-label">Panier</span>
                    <span id="compteur" class="panier-badge"><?= $cartCount ?></span>
                </a>
            </div>
        </div>
    </header>
    <?php
}

function renderSiteFooter(): void
{
    $items = getSiteNavigation();
    ?>
    <footer class="site-footer">
        <div class="site-footer-inner">
            <div class="site-footer-brand">
                <p class="site-footer-kicker">Bruxelles Notes</p>
                <p class="site-footer-copy">
                    Une maison de parfum editoriale qui melange elegance bruxelloise,
                    textures sensorielles et experience e-shop premium.
                </p>
            </div>

            <nav class="site-footer-nav" aria-label="Navigation footer">
                <?php foreach ($items as $item): ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="site-footer-link">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="site-footer-meta">
                <p>Signature collection 2026</p>
                <p>Bruxelles, Belgique</p>
            </div>
        </div>
    </footer>
    <?php
}
