<?php
require_once 'connexion.php';
require_once 'layout.php';

$cartCount = array_sum($_SESSION['panier'] ?? []);
$messageEnvoye = false;
$erreurs = [];
$nom = '';
$email = '';
$objet = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim((string) ($_POST['nom'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $objet = trim((string) ($_POST['objet'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($nom === '') {
        $erreurs[] = 'Merci de renseigner votre nom.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Merci de renseigner une adresse email valide.';
    }

    if ($message === '') {
        $erreurs[] = 'Merci de saisir votre message.';
    }

    if (empty($erreurs)) {
        $_SESSION['dernier_contact'] = [
            'nom' => $nom,
            'email' => $email,
            'objet' => $objet,
            'message' => $message,
            'date' => date('Y-m-d H:i:s'),
        ];
        $messageEnvoye = true;
        $nom = '';
        $email = '';
        $objet = '';
        $message = '';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bruxelles Notes - Contact</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Contactez Bruxelles Notes pour une demande presse, retail, marque ou direction artistique.">
</head>
<body>

    <?php renderSiteHeader('contact', $cartCount); ?>

    <main class="contact-page">
        <section class="editorial-hero">
            <div class="editorial-hero-copy">
                <p class="catalogue-kicker">Contact</p>
                <h1 class="home-display">Parler a la maison, a la marque ou au studio.</h1>
                <p class="home-lead">
                    Pour une question collection, une demande collaboration, une intention retail
                    ou un projet d image, cette page devient votre point d entree.
                </p>
            </div>
            <div class="editorial-hero-panel">
                <p>Presse</p>
                <p>Retail</p>
                <p>Studio</p>
                <p>Partenariats</p>
            </div>
        </section>

        <?php if ($messageEnvoye): ?>
            <p class="message contact-success">
                Merci. Votre message a bien ete pris en compte dans cette session de demo.
            </p>
        <?php endif; ?>

        <?php if (!empty($erreurs)): ?>
            <div class="message message--erreur">
                <?php foreach ($erreurs as $erreur): ?>
                    <p><?= htmlspecialchars($erreur) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="contact-layout">
            <section class="contact-form-card">
                <p class="catalogue-kicker">Nous ecrire</p>
                <h2>Envoyer un message</h2>

                <form method="post" action="contact.php" class="contact-form">
                    <label>
                        Nom
                        <input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Votre nom">
                    </label>

                    <label>
                        Email
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="vous@exemple.com">
                    </label>

                    <label>
                        Objet
                        <input type="text" name="objet" value="<?= htmlspecialchars($objet) ?>" placeholder="Collaboration, retail, presse...">
                    </label>

                    <label>
                        Message
                        <textarea name="message" rows="7" placeholder="Parlez-nous de votre demande..."><?= htmlspecialchars($message) ?></textarea>
                    </label>

                    <button type="submit" class="landing-btn landing-btn-primary">Envoyer</button>
                </form>
            </section>

            <aside class="contact-side">
                <article class="contact-info-card">
                    <p class="catalogue-kicker">Maison</p>
                    <h3>Bruxelles Notes</h3>
                    <p>Bruxelles, Belgique</p>
                    <p>hello@bruxellesnotes.be</p>
                </article>

                <article class="contact-info-card contact-info-dark">
                    <h3>Ce que nous pouvons imaginer ensemble</h3>
                    <ul class="contact-list">
                        <li>Lancements de collection</li>
                        <li>Collaborations artistiques</li>
                        <li>Distribution et retail</li>
                        <li>Direction visuelle de marque</li>
                    </ul>
                </article>
            </aside>
        </section>
    </main>

    <?php renderSiteFooter(); ?>

</body>
</html>
