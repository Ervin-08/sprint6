<?php
require_once 'connexion.php';
require_once 'layout.php';

$cartCount = array_sum($_SESSION['panier'] ?? []);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bruxelles Notes - About</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="About Bruxelles Notes : maison, vision, direction artistique et philosophie de collection.">
</head>
<body>

    <?php renderSiteHeader('about', $cartCount); ?>

    <main class="about-page">
        <section class="editorial-hero">
            <div class="editorial-hero-copy">
                <p class="catalogue-kicker">About Bruxelles Notes</p>
                <h1 class="home-display">Une maison qui transforme le parfum en langage visuel.</h1>
                <p class="home-lead">
                    Bruxelles Notes est pensee comme une marque complete : un ton, une silhouette,
                    une matiere et un rapport au temps. Le site en est l extension naturelle.
                </p>
            </div>
            <div class="editorial-hero-panel">
                <p>Bruxelles</p>
                <p>Textures</p>
                <p>Presence</p>
                <p>Elegance</p>
            </div>
        </section>

        <section class="about-grid">
            <article class="about-card">
                <p class="catalogue-kicker">Vision</p>
                <h2>Le luxe calme, jamais froid.</h2>
                <p>
                    La marque se situe entre boutique editoriale et maison de parfum contemporaine.
                    Nous cherchons une elegance chaude, tactile, presque cinematographique.
                </p>
            </article>

            <article class="about-card">
                <p class="catalogue-kicker">Methode</p>
                <h2>Des collections courtes, des profils nets.</h2>
                <p>
                    Peu de references, mais chacune defend un territoire olfactif identifiable :
                    fraicheur boisee, floral veloute, intensite nocturne ou lumiere citrus.
                </p>
            </article>
        </section>

        <section class="about-timeline">
            <article class="about-step">
                <span>01</span>
                <h3>Imaginer le sillage</h3>
                <p>Chaque parfum commence par une sensation, une scene, un lieu et une texture.</p>
            </article>
            <article class="about-step">
                <span>02</span>
                <h3>Composer l objet</h3>
                <p>Le flacon, la page, la lumiere et les mots doivent raconter la meme chose.</p>
            </article>
            <article class="about-step">
                <span>03</span>
                <h3>Orchestrer l experience</h3>
                <p>Le site devient un espace de marque coherent, pas seulement un catalogue.</p>
            </article>
        </section>

        <section class="about-quote-band">
            <p>
                “Une fragrance doit laisser une impression avant meme que ses notes soient nommees.”
            </p>
        </section>

        <section class="home-cta-band">
            <div>
                <p class="catalogue-kicker">Next step</p>
                <h2>Maintenant que la maison existe, l e-shop peut pleinement jouer son role.</h2>
            </div>
            <div class="landing-actions">
                <a href="produits.php" class="landing-btn landing-btn-primary">Explorer l e-shop</a>
                <a href="contact.php" class="landing-btn landing-btn-secondary">Contacter la maison</a>
            </div>
        </section>
    </main>

    <?php renderSiteFooter(); ?>

</body>
</html>
