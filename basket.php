<?php
require_once 'connexion.php';
require_once 'catalogue_helpers.php';
require_once 'layout.php';

$lignesPanier = [];
$totalArticles = 0;
$totalHtva = 0.0;
$erreur = null;

$panier = $_SESSION['panier'] ?? [];

if (!empty($panier)) {
    try {
        $pdo = getConnexion();

        foreach ($panier as $id => $quantite) {
            $stmt = $pdo->prepare('SELECT * FROM PRODUIT WHERE id_produit = :id');
            $stmt->execute(['id' => $id]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produit) {
                continue;
            }

            $prixHtva = (float) $produit['prix_produit'];
            $totalLigne = $prixHtva * $quantite;

            $lignesPanier[] = [
                'id' => (int) $id,
                'nom' => $produit['nom_produit'],
                'marque' => $produit['marque_produit'],
                'categorie' => $produit['categorie_produit'],
                'concentration' => $produit['concentration_parfum'],
                'image' => imageProduitParNom($produit['nom_produit']),
                'quantite' => (int) $quantite,
                'prixHtva' => $prixHtva,
                'totalLigne' => $totalLigne,
            ];

            $totalArticles += $quantite;
            $totalHtva += $totalLigne;
        }
    } catch (PDOException $e) {
        $erreur = 'Impossible de charger le panier. Veuillez reessayer.';
    }
}

$totalTvac = $totalHtva * (1 + TVA);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - Bruxelles Notes</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Recapitulatif de votre panier d achats Bruxelles Notes.">
</head>
<body>

    <?php renderSiteHeader('basket', $totalArticles); ?>

    <main class="basket-page" data-tva="<?= htmlspecialchars((string) TVA) ?>">
        <section class="basket-hero">
            <div>
                <p class="catalogue-kicker">Votre selection</p>
                <h2>Mon Panier</h2>
                <p class="basket-hero-copy">
                    Retrouvez vos parfums, verifiez les totaux et poursuivez votre selection
                    dans une presentation plus claire.
                </p>
            </div>
            <a href="produits.php" class="landing-btn landing-btn-secondary">Continuer mes achats</a>
        </section>

        <?php if ($erreur): ?>

            <p class="message message--erreur"><?= htmlspecialchars($erreur) ?></p>

        <?php elseif (empty($lignesPanier)): ?>

            <section class="basket-empty-state">
                <h3>Votre panier est vide</h3>
                <p>Explorez la collection pour ajouter un parfum a votre selection.</p>
                <a href="produits.php" class="landing-btn landing-btn-primary">Voir les produits</a>
            </section>

        <?php else: ?>

            <section class="basket-layout" aria-label="Recapitulatif du panier">
                <section class="basket-items-panel">
                    <div class="basket-panel-header">
                        <h3>Articles selectionnes</h3>
                        <p id="basket-count-label"><?= $totalArticles ?> article<?= $totalArticles > 1 ? 's' : '' ?></p>
                    </div>

                    <div class="basket-items-list">
                        <?php foreach ($lignesPanier as $ligne): ?>
                            <article class="basket-item tr-<?= $ligne['id'] ?>"
                                     data-quantite="<?= $ligne['quantite'] ?>"
                                     data-total-ligne="<?= htmlspecialchars((string) $ligne['totalLigne']) ?>">
                                <a href="produit.php?id=<?= $ligne['id'] ?>" class="basket-item-visual" aria-label="Voir <?= htmlspecialchars($ligne['nom']) ?>">
                                    <?php if ($ligne['image']): ?>
                                        <img src="<?= htmlspecialchars($ligne['image']) ?>" alt="<?= htmlspecialchars($ligne['nom']) ?>" class="panier-img">
                                    <?php else: ?>
                                        <div class="panier-placeholder" aria-hidden="true">
                                            <?= htmlspecialchars(initiale($ligne['nom'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </a>

                                <div class="basket-item-copy">
                                    <div>
                                        <p class="basket-item-brand"><?= htmlspecialchars($ligne['marque']) ?></p>
                                        <h3><?= htmlspecialchars($ligne['nom']) ?></h3>
                                        <p class="basket-item-meta">
                                            <?= htmlspecialchars($ligne['categorie']) ?> · <?= htmlspecialchars($ligne['concentration']) ?>
                                        </p>
                                    </div>

                                    <dl class="basket-item-pricing">
                                        <dt>Prix unitaire</dt>
                                        <dd><?= formatPrix($ligne['prixHtva']) ?></dd>
                                        <dt>Quantite</dt>
                                        <dd><?= $ligne['quantite'] ?></dd>
                                        <dt>Total ligne</dt>
                                        <dd><?= formatPrix($ligne['totalLigne']) ?></dd>
                                    </dl>
                                </div>

                                <button class="btn-suppr basket-remove-btn"
                                        onclick="suppr(<?= $ligne['id'] ?>)"
                                        type="button"
                                        title="Supprimer <?= htmlspecialchars($ligne['nom']) ?>">
                                    Supprimer
                                </button>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <aside class="basket-summary-panel" aria-label="Totaux du panier">
                    <h3>Recapitulatif</h3>
                    <dl class="panier-totaux">
                        <dt>Nombre d articles</dt>
                        <dd id="total-articles"><?= $totalArticles ?></dd>
                        <dt>Total HTVA</dt>
                        <dd id="total-htva"><?= formatPrix($totalHtva) ?></dd>
                        <dt>TVA (<?= (int) (TVA * 100) ?> %)</dt>
                        <dd id="total-tva"><?= formatPrix($totalTvac - $totalHtva) ?></dd>
                        <dt>Total TVAC</dt>
                        <dd id="total-tvac" class="prix-tvac"><?= formatPrix($totalTvac) ?></dd>
                    </dl>

                    <div class="basket-summary-note">
                        <p>Le panier se met a jour en direct et reste visible dans le header pendant toute la navigation.</p>
                    </div>
                </aside>
            </section>

        <?php endif; ?>
    </main>

    <?php renderSiteFooter(); ?>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="js/panier.js"></script>

</body>
</html>
