<?php
/* ============================================================
   Sprint 2 + Sprint 3 — Page produits dynamique
   MUST   : retrieveBuyableProducts() — liste depuis la DB
   SHOULD : retrieveCategories()      — catégories depuis la DB
            retrieveProducts()        — filtrage SQL par catégorie
   COULD  : retrieveProducts() avec tri par prix
   ============================================================ */

require_once 'connexion.php';

/* ── Fonctions métier ────────────────────────────────────── */

/**
 * Sprint 2 — MUST
 * Retourne tous les produits disponibles à la vente (stock > 0),
 * ordonnés par priorité de vente (id_produit).
 */
function retrieveBuyableProducts(PDO $pdo): array
{
    return retrieveProducts($pdo);
}

/**
 * Sprint 3 — SHOULD
 * Retourne toutes les catégories ayant au moins un produit disponible.
 */
function retrieveCategories(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT DISTINCT categorie_produit
           FROM PRODUIT
          WHERE stock_produit > 0
          ORDER BY categorie_produit ASC'
    );
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Sprint 3 — SHOULD + COULD
 * Retourne les produits disponibles, filtrés par catégorie(s) et triés.
 * Le filtre est appliqué en SQL (union, pas intersection).
 * Les catégories non reconnues sont ignorées.
 *
 * @param PDO    $pdo
 * @param array  $categories  Catégories validées (vide = toutes)
 * @param string $order       'asc' | 'desc' | '' (priorité de vente)
 */
function retrieveProducts(PDO $pdo, array $categories = [], string $order = ''): array
{
    $params = [];
    $sql    = 'SELECT * FROM PRODUIT WHERE stock_produit > 0';

    // Filtre catégories — union (OR), appliqué en SQL
    if (!empty($categories)) {
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $sql .= " AND categorie_produit IN ($placeholders)";
        $params = array_values($categories);
    }

    // Tri : prix croissant, décroissant, ou priorité de vente (défaut)
    if ($order === 'asc') {
        $sql .= ' ORDER BY prix_produit ASC';
    } elseif ($order === 'desc') {
        $sql .= ' ORDER BY prix_produit DESC';
    } else {
        $sql .= ' ORDER BY id_produit ASC';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ── Lecture et sécurisation des paramètres GET ─────────── */

$erreur                  = null;
$produits                = [];
$categoriesDisponibles   = [];
$categoriesSelectionnees = [];
$ordrePrix               = '';

try {
    $pdo = getConnexion();

    // 1. Catégories depuis la DB (Sprint 3 SHOULD)
    $categoriesDisponibles = retrieveCategories($pdo);

    // 2. Valider les catégories soumises (whitelist contre la DB)
    if (!empty($_GET['categories']) && is_array($_GET['categories'])) {
        foreach ($_GET['categories'] as $cat) {
            $cat = (string) $cat;
            if (in_array($cat, $categoriesDisponibles, true)) {
                $categoriesSelectionnees[] = $cat;
            }
            // Catégorie non reconnue → ignorée (spec Sprint 3)
        }
    }

    // 3. Valider le tri par prix (whitelist)
    if (isset($_GET['ordre_prix']) && in_array($_GET['ordre_prix'], ['asc', 'desc'], true)) {
        $ordrePrix = $_GET['ordre_prix'];
    }

    // 4. Récupérer les produits filtrés et triés
    $produits = retrieveProducts($pdo, $categoriesSelectionnees, $ordrePrix);

} catch (PDOException $e) {
    $erreur = 'Impossible de charger les produits. Veuillez réessayer plus tard.';
}

/* ── Helpers d'affichage ─────────────────────────────────── */

function formatPrix(float $prix): string
{
    return number_format($prix, 2, ',', ' ') . ' €';
}

function initiale(string $nom): string
{
    return mb_strtoupper(mb_substr($nom, 0, 1, 'UTF-8'), 'UTF-8');
}

function stockClasse(int $stock): string
{
    if ($stock <= 10) return 'stock-indicateur stock-faible';
    return 'stock-indicateur';
}

function stockLibelle(int $stock): string
{
    if ($stock <= 10) return 'Stock limité (' . $stock . ')';
    return 'En stock (' . $stock . ')';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique Parfums — Nos Produits</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Découvrez notre sélection de parfums : Femme, Homme, Unisexe.">
</head>
<body>

    <header>
        <h1>Boutique Parfums</h1>
        <nav aria-label="Navigation principale">
            <a href="produits.php">Produits</a>
        </nav>
        <nav aria-label="Navigation secondaire">
            <a href="basket.php" title="Mon panier" class="nav-panier">
                🛒 Panier
                <span id="compteur" class="panier-badge">
                    <?= array_sum($_SESSION['panier'] ?? []) ?>
                </span>
            </a>
        </nav>
    </header>

    <main>
        <h2>Nos Produits</h2>

        <?php if ($erreur): ?>

            <p class="message message--erreur"><?= htmlspecialchars($erreur) ?></p>

        <?php elseif (empty($produits)): ?>

            <p class="message message--erreur">Aucun produit disponible pour le moment.</p>

        <?php else: ?>

            <!-- ── Formulaire de filtres (Sprint 3 SHOULD + COULD) ──
                 Catégories chargées depuis la DB.
                 État du formulaire conservé après soumission (GET).
                 La liste se met à jour à la soumission.
            ──────────────────────────────────────────────────────── -->
            <form class="filtres-form" method="get" action="produits.php"
                  aria-label="Options d'affichage des produits">

                <fieldset class="filtres-fieldset">
                    <legend class="filtres-legend">Filtrer par catégorie</legend>

                    <?php foreach ($categoriesDisponibles as $cat):
                        $checked = in_array($cat, $categoriesSelectionnees, true) ? 'checked' : '';
                    ?>
                        <label class="filtre-checkbox">
                            <input type="checkbox"
                                   name="categories[]"
                                   value="<?= htmlspecialchars($cat) ?>"
                                   <?= $checked ?>>
                            <?= htmlspecialchars($cat) ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>

                <fieldset class="filtres-fieldset">
                    <legend class="filtres-legend">Trier par prix</legend>

                    <label class="filtre-select-label" for="ordre-prix">Ordre :</label>
                    <select id="ordre-prix" name="ordre_prix" class="filtre-select">
                        <option value=""      <?= $ordrePrix === ''     ? 'selected' : '' ?>>Priorité de vente (défaut)</option>
                        <option value="asc"   <?= $ordrePrix === 'asc'  ? 'selected' : '' ?>>Prix croissant</option>
                        <option value="desc"  <?= $ordrePrix === 'desc' ? 'selected' : '' ?>>Prix décroissant</option>
                    </select>
                </fieldset>

                <button type="submit" class="filtres-btn-appliquer">
                    Appliquer les filtres
                </button>

            </form>

            <!-- ── Grille des produits ──────────────────────────── -->
            <section id="liste-produits" aria-label="Liste des produits">

                <?php foreach ($produits as $produit):
                    $id           = (int) $produit['id_produit'];
                    $nom          = htmlspecialchars($produit['nom_produit']);
                    $marque       = htmlspecialchars($produit['marque_produit']);
                    $categorie    = htmlspecialchars($produit['categorie_produit']);
                    $concentration = htmlspecialchars($produit['concentration_parfum']);
                    $description  = htmlspecialchars($produit['description_produit']);
                    $prix         = formatPrix((float) $produit['prix_produit']);
                    $stock        = (int) $produit['stock_produit'];
                ?>
                <article class="carte-produit">

                    <a href="produit.php?id=<?= $id ?>"
                       title="Voir la fiche de <?= $nom ?>">

                        <!-- Visuel : image réelle si disponible, sinon placeholder -->
                        <?php
                        $imagesProduits = [
                            'bleu intense' => 'images/bleu_intense.png',
                            'rose élégante' => 'images/rose_elegante.png',
                            'citrus energy' => 'images/citrus_energy.png',
                            'nuit mystérieuse' => 'images/nuit_mysterieuse.png',
                            'océan sport' => 'images/ocean_sport.png',
                            'vanilla dream' => 'images/vanilla_dream.png'
                            
                            
                        ];
                        $nomCle    = mb_strtolower(trim($produit['nom_produit']), 'UTF-8');
                        $imgPath   = $imagesProduits[$nomCle] ?? null;
                        ?>
                        <div class="produit-visuel" aria-hidden="true">
                            <?php if ($imgPath): ?>
                                <img src="<?= htmlspecialchars($imgPath) ?>"
                                     alt="<?= $nom ?>"
                                     class="produit-img">
                            <?php else: ?>
                                <span class="initiale"><?= initiale($nom) ?></span>
                                <span class="concentration-label"><?= $concentration ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Informations produit -->
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

        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 Boutique Parfums. Tous droits réservés.</p>
    </footer>

</body>
</html>
