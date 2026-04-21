<?php
require_once 'connexion.php';
require_once 'catalogue_helpers.php';
require_once 'layout.php';

$erreur = null;
$produits = [];

try {
    $pdo = getConnexion();
    $produits = array_slice(retrieveBuyableProducts($pdo), 0, 4);
} catch (PDOException $e) {
    $erreur = 'Impossible de charger la selection pour le moment.';
}

$cartCount = array_sum($_SESSION['panier'] ?? []);
$vedette = $produits[0] ?? null;
$galerieHero = [];

foreach ($produits as $produit) {
    $image = imageProduitParNom($produit['nom_produit']);
    if ($image) {
        $galerieHero[] = [
            'image' => $image,
            'nom' => $produit['nom_produit'],
            'marque' => $produit['marque_produit'],
            'categorie' => $produit['categorie_produit'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bruxelles Notes - Accueil</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Bruxelles Notes, maison de parfum editoriale : accueil, collection, histoire de marque et experience e-shop premium.">
</head>
<body>

    <?php renderSiteHeader('home', $cartCount); ?>

    <main class="home-page">
        <section class="home-hero">
            <div class="home-hero-copy">
                <span class="landing-pill">Maison de parfum</span>
                <h1 class="home-display">Bruxelles Notes compose une signature qui habille la presence.</h1>
                <p class="home-lead">
                    Entre elegance editoriale, textures sensorielles et selection fine, la marque imagine
                    un e-shop ou chaque parfum devient une prise de position.
                </p>
                <div class="landing-actions">
                    <a href="produits.php" class="landing-btn landing-btn-primary">Entrer dans l e-shop</a>
                    <a href="about.php" class="landing-btn landing-btn-secondary">Decouvrir la maison</a>
                </div>
            </div>

            <div class="home-hero-visual">
                <?php if (!empty($galerieHero)): ?>
                    <section class="media-gallery" data-gallery data-autoplay="true" aria-label="Galerie de produits Bruxelles Notes">
                        <div class="media-gallery-track">
                            <?php foreach ($galerieHero as $index => $slide): ?>
                                <figure class="media-gallery-slide <?= $index === 0 ? 'is-active' : '' ?>">
                                    <img src="<?= htmlspecialchars($slide['image']) ?>"
                                         alt="<?= htmlspecialchars($slide['nom']) ?>"
                                         class="home-hero-image">
                                    <figcaption class="media-gallery-caption">
                                        <span class="media-gallery-title"><?= htmlspecialchars($slide['nom']) ?></span>
                                        <span class="media-gallery-meta"><?= htmlspecialchars($slide['marque']) ?> · <?= htmlspecialchars($slide['categorie']) ?></span>
                                    </figcaption>
                                </figure>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="media-gallery-arrow media-gallery-arrow-prev" data-gallery-prev aria-label="Image precedente">
                            &#8249;
                        </button>
                        <button type="button" class="media-gallery-arrow media-gallery-arrow-next" data-gallery-next aria-label="Image suivante">
                            &#8250;
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
                <?php elseif ($vedette): ?>
                    <?php $imageVedette = imageProduitParNom($vedette['nom_produit']); ?>
                    <?php if ($imageVedette): ?>
                        <img src="<?= htmlspecialchars($imageVedette) ?>" alt="<?= htmlspecialchars($vedette['nom_produit']) ?>" class="home-hero-image">
                    <?php else: ?>
                        <div class="landing-hero-placeholder">
                            <span class="initiale"><?= initiale($vedette['nom_produit']) ?></span>
                            <span class="concentration-label"><?= htmlspecialchars($vedette['concentration_parfum']) ?></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="home-marquee">
            <p>Bruxelles Notes · e-shop premium · fragrances editoriales · collection signature · maison de parfum · studio de contact</p>
        </section>

        <section class="home-manifesto-grid">
            <article class="home-manifesto-card">
                <p class="catalogue-kicker">Manifeste</p>
                <h2>Une marque qui traite le parfum comme un objet culturel.</h2>
                <p>
                    Nous ne vendons pas seulement des flacons : nous construisons une presence.
                    Chaque page du site prolonge l univers de la maison avec plus de coherence et de desir.
                </p>
            </article>

            <article class="home-metric-card">
                <span>06</span>
                <p>fragrances signatures composent la collection actuelle.</p>
            </article>

            <article class="home-metric-card">
                <span>03</span>
                <p>piliers structurent la maison : elegance, texture, memorabilite.</p>
            </article>
        </section>

        <section class="home-editorial-grid">
            <article class="home-editorial-copy">
                <p class="catalogue-kicker">Signature</p>
                <h2>Le parfum comme direction artistique.</h2>
                <p>
                    Bruxelles Notes puise dans la ville, les interieurs feutres, les bois clairs,
                    les petales poudres et les matieres qui laissent une trace. Le site prolonge
                    cette sensation avec une navigation plus editoriale qu utilitaire.
                </p>
            </article>

            <article class="home-dark-panel">
                <h3>Le site doit donner envie avant meme l achat.</h3>
                <p>
                    C est pour cela que l accueil, l e-shop, l histoire de marque et la prise de contact
                    parlent ici dans un seul et meme langage visuel.
                </p>
            </article>
        </section>

        <section class="home-featured-section">
            <div class="catalogue-header">
                <div>
                    <p class="catalogue-kicker">Selection maison</p>
                    <h2>Les parfums a explorer</h2>
                </div>
                <a href="produits.php" class="landing-btn landing-btn-secondary">Voir tout le catalogue</a>
            </div>

            <?php if ($erreur): ?>
                <p class="message message--erreur"><?= htmlspecialchars($erreur) ?></p>
            <?php else: ?>
                <section id="liste-produits" aria-label="Selection de produits">
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
                                        <span class="<?= stockClasse($stock) ?>"><?= stockLibelle($stock) ?></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </section>

        <section class="home-cta-band">
            <div>
                <p class="catalogue-kicker">Bruxelles Notes World</p>
                <h2>Accueil, e-shop, contact et histoire de marque parlent enfin la meme langue.</h2>
            </div>
            <div class="landing-actions">
                <a href="about.php" class="landing-btn landing-btn-secondary">Notre histoire</a>
                <a href="contact.php" class="landing-btn landing-btn-primary">Parler a la maison</a>
            </div>
        </section>
    </main>

    <?php renderSiteFooter(); ?>

    <?php if (!empty($galerieHero)): ?>
        <script src="js/gallery.js"></script>
    <?php endif; ?>

</body>
</html>
