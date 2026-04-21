<?php
require_once 'connexion.php';
require_once 'catalogue_helpers.php';
require_once 'layout.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$produit = [];
$erreur = null;
$avertissement = null;
$produitsAssocies = [];

if ($id <= 0) {
    $erreur = 'Identifiant de produit invalide.';
} else {
    try {
        $pdo = getConnexion();
        $produit = retrieveProductById($pdo, $id);

        if (empty($produit)) {
            $erreur = 'Ce produit est introuvable.';
        } else {
            if (!isProductAvailable($produit)) {
                $avertissement = 'Ce produit n est actuellement pas disponible a la vente.';
            }
            $produitsAssocies = retrieveRelatedProducts($pdo, $produit);
        }
    } catch (PDOException $e) {
        $erreur = 'Impossible de charger le produit. Veuillez reessayer plus tard.';
    }
}

$prixHtva = null;
$prixTvac = null;

if (!empty($produit)) {
    $prixHtva = (float) $produit['prix_produit'];
    $prixTvac = $prixHtva * (1 + TVA);
}

$titrePage = !empty($produit)
    ? htmlspecialchars($produit['nom_produit']) . ' - Bruxelles Notes'
    : 'Produit introuvable - Bruxelles Notes';
$cartCount = array_sum($_SESSION['panier'] ?? []);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titrePage ?></title>
    <link rel="stylesheet" href="style.css">
    <?php if (!empty($produit)): ?>
        <meta name="description" content="<?= htmlspecialchars($produit['description_produit']) ?>">
    <?php endif; ?>
</head>
<body>

    <?php renderSiteHeader('shop', $cartCount); ?>

    <main class="detail-page">
        <a href="produits.php" class="lien-retour" title="Retour a la liste des produits">
            Retour aux produits
        </a>

        <?php if ($erreur): ?>

            <p class="message message--erreur"><?= htmlspecialchars($erreur) ?></p>

        <?php else: ?>

            <?php if ($avertissement): ?>
                <p class="message message--avertissement"><?= htmlspecialchars($avertissement) ?></p>
            <?php endif; ?>

            <?php
            $nom = htmlspecialchars($produit['nom_produit']);
            $marque = htmlspecialchars($produit['marque_produit']);
            $categorie = htmlspecialchars($produit['categorie_produit']);
            $concentration = htmlspecialchars($produit['concentration_parfum']);
            $description = htmlspecialchars($produit['description_produit']);
            $stock = (int) $produit['stock_produit'];
            $imagePath = imageProduitParNom($produit['nom_produit']);
            $profil = profilOlfactif($produit);
            ?>

            <section class="detail-hero" aria-label="Fiche du produit <?= $nom ?>">
                <div class="detail-copy">
                    <p class="detail-kicker"><?= $marque ?> · <?= $categorie ?></p>
                    <h2><?= $nom ?></h2>
                    <p class="detail-subtitle">
                        Une composition <?= mb_strtolower($categorie, 'UTF-8') ?> avec un sillage
                        <?= mb_strtolower($concentration, 'UTF-8') ?> pense pour durer.
                    </p>

                    <div class="detail-badges">
                        <span class="badge badge-categorie"><?= $categorie ?></span>
                        <span class="badge"><?= $concentration ?></span>
                        <span class="badge"><?= $stock > 0 ? 'Disponible' : 'Indisponible' ?></span>
                    </div>
                </div>

                <div class="detail-panel">
                    <div class="detail-visual-card">
                        <?php if ($imagePath): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>"
                                 alt="<?= $nom ?>"
                                 class="fiche-img detail-main-image"
                                 loading="eager">
                        <?php else: ?>
                            <div class="fiche-placeholder detail-main-placeholder">
                                <span class="fiche-initiale"><?= initiale($produit['nom_produit']) ?></span>
                                <span class="fiche-concentration"><?= $concentration ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <aside class="detail-summary-card">
                        <section class="detail-summary-section">
                            <h3>Fragrance Notes</h3>
                            <dl class="landing-notes-list">
                                <dt>Top Notes</dt>
                                <dd><?= htmlspecialchars($profil['top']) ?></dd>
                                <dt>Heart Notes</dt>
                                <dd><?= htmlspecialchars($profil['heart']) ?></dd>
                                <dt>Base Notes</dt>
                                <dd><?= htmlspecialchars($profil['base']) ?></dd>
                            </dl>
                        </section>

                        <section class="detail-summary-section">
                            <h3>Prix</h3>
                            <dl class="detail-price-grid">
                                <dt>Prix HTVA</dt>
                                <dd><?= formatPrix($prixHtva) ?></dd>
                                <dt>TVA (<?= (int) (TVA * 100) ?> %)</dt>
                                <dd><?= formatPrix($prixTvac - $prixHtva) ?></dd>
                                <dt>Prix TVAC</dt>
                                <dd class="prix-tvac"><?= formatPrix($prixTvac) ?></dd>
                            </dl>
                        </section>

                        <section class="detail-summary-section">
                            <h3>Disponibilite</h3>
                            <?php if ($stock > 0): ?>
                                <p><strong class="stock-ok">En stock</strong> - <?= $stock ?> unite<?= $stock > 1 ? 's' : '' ?> disponible<?= $stock > 1 ? 's' : '' ?></p>
                            <?php else: ?>
                                <p><strong class="stock-vide">Rupture</strong> - Produit temporairement indisponible.</p>
                            <?php endif; ?>
                        </section>

                        <?php if (isProductAvailable($produit)): ?>
                            <?php $qteMax = min($stock, 5); ?>
                            <section class="detail-summary-section">
                                <h3>Ajouter au panier</h3>

                                <form id="form-ajout-panier" class="form-ajout-panier detail-form-ajout" data-id-produit="<?= $id ?>">
                                    <label for="quantite">Quantite</label>
                                    <select id="quantite" name="quantite">
                                        <?php for ($i = 1; $i <= $qteMax; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>

                                    <button type="submit" class="btn-ajout-panier">
                                        Ajouter au panier
                                    </button>
                                </form>

                                <div id="ajout-feedback" role="status" aria-live="polite"></div>
                            </section>
                        <?php endif; ?>
                    </aside>
                </div>
            </section>

            <section class="detail-content-grid" aria-label="Informations detaillees">
                <article class="detail-story-card">
                    <h3>Description</h3>
                    <p><?= $description ?></p>
                </article>

                <article class="detail-story-card">
                    <h3>Identite</h3>
                    <ul class="detail-feature-list">
                        <li><span>Marque</span><strong><?= $marque ?></strong></li>
                        <li><span>Categorie</span><strong><?= $categorie ?></strong></li>
                        <li><span>Concentration</span><strong><?= $concentration ?></strong></li>
                    </ul>
                </article>

                <article class="detail-dark-card">
                    <h3>Pourquoi ce parfum</h3>
                    <p>
                        Une fiche plus claire, un achat plus rapide et un univers plus premium autour de chaque
                        parfum pour mieux valoriser votre catalogue.
                    </p>
                </article>
            </section>

            <?php if (!empty($produitsAssocies)): ?>
                <section class="related-section" aria-label="Produits associes">
                    <div class="catalogue-header">
                        <div>
                            <p class="catalogue-kicker">A decouvrir aussi</p>
                            <h2>Vous aimerez aussi</h2>
                        </div>
                    </div>

                    <section id="liste-produits" aria-label="Produits associes">
                        <?php foreach ($produitsAssocies as $associe):
                            $associeId = (int) $associe['id_produit'];
                            $associeNom = htmlspecialchars($associe['nom_produit']);
                            $associeMarque = htmlspecialchars($associe['marque_produit']);
                            $associeCategorie = htmlspecialchars($associe['categorie_produit']);
                            $associeConcentration = htmlspecialchars($associe['concentration_parfum']);
                            $associeDescription = htmlspecialchars($associe['description_produit']);
                            $associePrix = formatPrix((float) $associe['prix_produit']);
                            $associeStock = (int) $associe['stock_produit'];
                            $associeImage = imageProduitParNom($associe['nom_produit']);
                        ?>
                            <article class="carte-produit">
                                <a href="produit.php?id=<?= $associeId ?>" title="Voir la fiche de <?= $associeNom ?>">
                                    <div class="produit-visuel" aria-hidden="true">
                                        <?php if ($associeImage): ?>
                                            <img src="<?= htmlspecialchars($associeImage) ?>" alt="<?= $associeNom ?>" class="produit-img" loading="lazy">
                                        <?php else: ?>
                                            <span class="initiale"><?= initiale($associe['nom_produit']) ?></span>
                                            <span class="concentration-label"><?= $associeConcentration ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="carte-corps">
                                        <h3><?= $associeNom ?></h3>
                                        <p class="carte-marque"><?= $associeMarque ?></p>
                                        <p class="carte-description"><?= $associeDescription ?></p>
                                        <div class="badges">
                                            <span class="badge badge-categorie"><?= $associeCategorie ?></span>
                                            <span class="badge"><?= $associeConcentration ?></span>
                                        </div>
                                        <div class="carte-pied">
                                            <span class="carte-prix"><?= $associePrix ?></span>
                                            <span class="<?= stockClasse($associeStock) ?>">
                                                <?= stockLibelle($associeStock) ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </section>
                </section>
            <?php endif; ?>

        <?php endif; ?>
    </main>

    <?php renderSiteFooter(); ?>

    <?php if (!empty($produit) && isProductAvailable($produit)): ?>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="js/produit.js"></script>
    <?php endif; ?>

</body>
</html>
