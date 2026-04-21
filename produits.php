<?php
require_once 'connexion.php';
require_once 'catalogue_helpers.php';
require_once 'layout.php';

$erreur = null;
$produits = [];
$categoriesDisponibles = [];
$categoriesSelectionnees = [];
$ordrePrix = '';

try {
    $pdo = getConnexion();
    $categoriesDisponibles = retrieveCategories($pdo);

    if (!empty($_GET['categories']) && is_array($_GET['categories'])) {
        foreach ($_GET['categories'] as $cat) {
            $cat = (string) $cat;
            if (in_array($cat, $categoriesDisponibles, true)) {
                $categoriesSelectionnees[] = $cat;
            }
        }
    }

    if (isset($_GET['ordre_prix']) && in_array($_GET['ordre_prix'], ['asc', 'desc'], true)) {
        $ordrePrix = $_GET['ordre_prix'];
    }

    $produits = retrieveProducts($pdo, $categoriesSelectionnees, $ordrePrix);
} catch (PDOException $e) {
    $erreur = 'Impossible de charger les produits. Veuillez reessayer plus tard.';
}

$cartCount = array_sum($_SESSION['panier'] ?? []);
$galerieHero = [];

foreach ($produits as $produitGalerie) {
    $image = imageProduitParNom($produitGalerie['nom_produit']);
    if ($image) {
        $galerieHero[] = [
            'image' => $image,
            'nom' => $produitGalerie['nom_produit'],
            'marque' => $produitGalerie['marque_produit'],
            'categorie' => $produitGalerie['categorie_produit'],
        ];
    }

    if (count($galerieHero) === 4) {
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bruxelles Notes - E-shop</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Decouvrez la collection Bruxelles Notes, nos parfums signatures et notre e-shop premium.">
</head>
<body>

    <?php renderSiteHeader('shop', $cartCount); ?>

    <main class="landing-produits">
        <?php if ($erreur): ?>

            <p class="message message--erreur"><?= htmlspecialchars($erreur) ?></p>

        <?php elseif (empty($produits)): ?>

            <p class="message message--erreur">Aucun produit disponible pour le moment.</p>

        <?php else: ?>

            <?php
            $produitVedette = $produits[0];
            $produitSecondaire = $produits[1] ?? $produitVedette;
            $imageVedette = imageProduitParNom($produitVedette['nom_produit']);
            $imageSecondaire = imageProduitParNom($produitSecondaire['nom_produit']);
            $profilVedette = profilOlfactif($produitVedette);
            $prixVedette = formatPrix((float) $produitVedette['prix_produit']);
            $stockVedette = (int) $produitVedette['stock_produit'];
            ?>

            <section class="landing-shell" aria-label="Presentation de la collection">
                <div class="landing-topbar">
                    <span class="landing-menu">E-shop</span>
                    <div class="landing-brand-block">
                        <span class="landing-brand">Bruxelles Notes</span>
                        <span class="landing-brand-sub">Collection signature</span>
                    </div>
                    <p class="landing-kicker">Une presence elegante, construite autour de votre selection.</p>
                </div>

                <section class="landing-hero" aria-label="Produit vedette">
                    <div class="landing-hero-copy">
                        <span class="landing-pill">Only for you</span>
                        <h2>Un sillage qui reste encore apres votre passage.</h2>
                        <p class="landing-intro">
                            Inspiree par des instants de douceur, de confiance et de presence, notre collection
                            met en avant <?= htmlspecialchars($produitVedette['nom_produit']) ?> pour ouvrir la visite
                            avec votre univers parfum.
                        </p>

                        <div class="landing-actions">
                            <a href="produit.php?id=<?= (int) $produitVedette['id_produit'] ?>" class="landing-btn landing-btn-primary">
                                Acheter
                            </a>
                            <a href="#liste-produits" class="landing-btn landing-btn-secondary">
                                Voir la collection
                            </a>
                        </div>
                    </div>

                    <div class="landing-hero-visual">
                        <?php if (!empty($galerieHero)): ?>
                            <section class="media-gallery" data-gallery data-autoplay="true" aria-label="Galerie de la collection">
                                <div class="media-gallery-track">
                                    <?php foreach ($galerieHero as $index => $slide): ?>
                                        <figure class="media-gallery-slide <?= $index === 0 ? 'is-active' : '' ?>">
                                            <img src="<?= htmlspecialchars($slide['image']) ?>"
                                                 alt="<?= htmlspecialchars($slide['nom']) ?>"
                                                 class="landing-hero-image">
                                            <figcaption class="media-gallery-caption">
                                                <span class="media-gallery-title"><?= htmlspecialchars($slide['nom']) ?></span>
                                                <span class="media-gallery-meta"><?= htmlspecialchars($slide['marque']) ?> · <?= htmlspecialchars($slide['categorie']) ?></span>
                                            </figcaption>
                                        </figure>
                                    <?php endforeach; ?>
                                </div>

                                <button type="button" class="media-gallery-arrow media-gallery-arrow-prev" data-gallery-prev aria-label="Image precedente">
                                    ‹
                                </button>
                                <button type="button" class="media-gallery-arrow media-gallery-arrow-next" data-gallery-next aria-label="Image suivante">
                                    ›
                                </button>

                                <div class="media-gallery-dots" role="tablist" aria-label="Navigation de la galerie">
                                    <?php foreach ($galerieHero as $index => $slide): ?>
                                        <button type="button"
                                                class="media-gallery-dot <?= $index === 0 ? 'is-active' : '' ?>"
                                                data-gallery-dot="<?= $index ?>"
                                                aria-label="Aller a l image <?= $index + 1 ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php elseif ($imageVedette): ?>
                            <img src="<?= htmlspecialchars($imageVedette) ?>"
                                 alt="<?= htmlspecialchars($produitVedette['nom_produit']) ?>"
                                 class="landing-hero-image">
                        <?php else: ?>
                            <div class="landing-hero-placeholder">
                                <span class="initiale"><?= initiale($produitVedette['nom_produit']) ?></span>
                                <span class="concentration-label"><?= htmlspecialchars($produitVedette['concentration_parfum']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <aside class="landing-side" aria-label="Notes olfactives">
                        <section class="landing-notes-card">
                            <h3>Fragrance Notes</h3>
                            <dl class="landing-notes-list">
                                <dt>Top Notes</dt>
                                <dd><?= htmlspecialchars($profilVedette['top']) ?></dd>
                                <dt>Heart Notes</dt>
                                <dd><?= htmlspecialchars($profilVedette['heart']) ?></dd>
                                <dt>Base Notes</dt>
                                <dd><?= htmlspecialchars($profilVedette['base']) ?></dd>
                            </dl>
                        </section>

                        <section class="landing-side-visual">
                            <?php if ($imageSecondaire): ?>
                                <img src="<?= htmlspecialchars($imageSecondaire) ?>"
                                     alt="<?= htmlspecialchars($produitSecondaire['nom_produit']) ?>"
                                     class="landing-side-image">
                            <?php else: ?>
                                <div class="landing-side-placeholder">
                                    <span class="initiale"><?= initiale($produitSecondaire['nom_produit']) ?></span>
                                </div>
                            <?php endif; ?>
                        </section>

                        <section class="landing-values-card">
                            <ul>
                                <li>Selection disponible en temps reel</li>
                                <li>Petites collections aux profils differents</li>
                                <li>Panier visible partout pendant la navigation</li>
                            </ul>
                        </section>
                    </aside>
                </section>

                <section class="landing-story-grid" aria-label="Presentation editoriale">
                    <article class="landing-story-copy">
                        <h3>Fragrance that leaves your presence behind.</h3>
                        <p>
                            Bruxelles Notes imagine un e-shop ou l achat devient une experience de marque.
                            Chaque parfum melange esthetique editoriale, details sensoriels et navigation claire.
                        </p>
                    </article>

                    <article class="landing-story-notes">
                        <h3>Produit vedette</h3>
                        <p class="landing-story-product">
                            <?= htmlspecialchars($produitVedette['nom_produit']) ?> - <?= htmlspecialchars($produitVedette['marque_produit']) ?>
                        </p>
                        <dl class="landing-story-meta">
                            <dt>Categorie</dt>
                            <dd><?= htmlspecialchars($produitVedette['categorie_produit']) ?></dd>
                            <dt>Concentration</dt>
                            <dd><?= htmlspecialchars($produitVedette['concentration_parfum']) ?></dd>
                            <dt>Prix</dt>
                            <dd><?= htmlspecialchars($prixVedette) ?></dd>
                            <dt>Disponibilite</dt>
                            <dd><?= htmlspecialchars(stockLibelle($stockVedette)) ?></dd>
                        </dl>
                    </article>

                    <article class="landing-story-panel">
                        <h3>Why choose Bruxelles Notes</h3>
                        <p>
                            Une direction artistique plus forte, des fiches detaillees, une navigation premium
                            et un univers de marque plus memorisable.
                        </p>
                    </article>
                </section>

                <section class="catalogue-section" aria-label="Catalogue de parfums">
                    <div class="catalogue-header">
                        <div>
                            <p class="catalogue-kicker">Collection complete</p>
                            <h2>Nos Produits</h2>
                        </div>

                        <form class="filtres-form" method="get" action="produits.php" aria-label="Options d affichage des produits">
                            <fieldset class="filtres-fieldset">
                                <legend class="filtres-legend">Filtrer par categorie</legend>

                                <?php foreach ($categoriesDisponibles as $cat):
                                    $checked = in_array($cat, $categoriesSelectionnees, true) ? 'checked' : '';
                                ?>
                                    <label class="filtre-checkbox">
                                        <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($cat) ?>" <?= $checked ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>

                            <fieldset class="filtres-fieldset">
                                <legend class="filtres-legend">Trier par prix</legend>
                                <label class="filtre-select-label" for="ordre-prix">Ordre :</label>
                                <select id="ordre-prix" name="ordre_prix" class="filtre-select">
                                    <option value="" <?= $ordrePrix === '' ? 'selected' : '' ?>>Priorite de vente</option>
                                    <option value="asc" <?= $ordrePrix === 'asc' ? 'selected' : '' ?>>Prix croissant</option>
                                    <option value="desc" <?= $ordrePrix === 'desc' ? 'selected' : '' ?>>Prix decroissant</option>
                                </select>
                            </fieldset>

                            <button type="submit" class="filtres-btn-appliquer">Appliquer les filtres</button>
                        </form>
                    </div>

                    <section id="liste-produits" aria-label="Liste des produits">
                        <?php foreach ($produits as $produit):
                            $id = (int) $produit['id_produit'];
                            $nom = htmlspecialchars($produit['nom_produit']);
                            $marque = htmlspecialchars($produit['marque_produit']);
                            $categorie = htmlspecialchars($produit['categorie_produit']);
                            $concentration = htmlspecialchars($produit['concentration_parfum']);
                            $description = htmlspecialchars($produit['description_produit']);
                            $prix = formatPrix((float) $produit['prix_produit']);
                            $stock = (int) $produit['stock_produit'];
                            $imgPath = imageProduitParNom($produit['nom_produit']);
                        ?>
                            <article class="carte-produit">
                                <a href="produit.php?id=<?= $id ?>" title="Voir la fiche de <?= $nom ?>">
                                    <div class="produit-visuel" aria-hidden="true">
                                        <?php if ($imgPath): ?>
                                            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= $nom ?>" class="produit-img" loading="lazy">
                                        <?php else: ?>
                                            <span class="initiale"><?= initiale($produit['nom_produit']) ?></span>
                                            <span class="concentration-label"><?= $concentration ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="carte-corps">
                                        <h3><?= $nom ?></h3>
                                        <p class="carte-marque"><?= $marque ?></p>
                                        <p class="carte-description"><?= $description ?></p>

                                        <div class="badges">
                                            <span class="badge badge-categorie"><?= $categorie ?></span>
                                            <span class="badge"><?= $concentration ?></span>
                                        </div>

                                        <div class="carte-pied">
                                            <span class="carte-prix"><?= $prix ?></span>
                                            <span class="<?= stockClasse($stock) ?>">
                                                <?= stockLibelle($stock) ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </section>
                </section>
            </section>

        <?php endif; ?>
    </main>

    <?php renderSiteFooter(); ?>

    <?php if (!empty($galerieHero)): ?>
        <script src="js/gallery.js"></script>
    <?php endif; ?>

</body>
</html>
