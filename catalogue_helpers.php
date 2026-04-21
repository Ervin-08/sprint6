<?php

function retrieveBuyableProducts(PDO $pdo): array
{
    return retrieveProducts($pdo);
}

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

function retrieveProducts(PDO $pdo, array $categories = [], string $order = ''): array
{
    $params = [];
    $sql = 'SELECT * FROM PRODUIT WHERE stock_produit > 0';

    if (!empty($categories)) {
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $sql .= " AND categorie_produit IN ($placeholders)";
        $params = array_values($categories);
    }

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

function retrieveProductById(PDO $pdo, $id): array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM PRODUIT WHERE id_produit = :id LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    return $produit ?: [];
}

function retrieveRelatedProducts(PDO $pdo, array $produit, int $limit = 3): array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM PRODUIT
         WHERE stock_produit > 0
           AND id_produit != :id
           AND categorie_produit = :categorie
         ORDER BY id_produit ASC
         LIMIT ' . (int) $limit
    );

    $stmt->execute([
        ':id' => (int) $produit['id_produit'],
        ':categorie' => $produit['categorie_produit']
    ]);

    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultats) >= $limit) {
        return $resultats;
    }

    $stmt = $pdo->prepare(
        'SELECT * FROM PRODUIT
         WHERE stock_produit > 0
           AND id_produit != :id
         ORDER BY id_produit ASC
         LIMIT ' . (int) $limit
    );
    $stmt->execute([':id' => (int) $produit['id_produit']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isProductAvailable(array $produit): bool
{
    $enStock = (int) $produit['stock_produit'] > 0;
    $dispVente = !isset($produit['statut_produit']) || (int) $produit['statut_produit'] === 1;
    return $enStock && $dispVente;
}

function formatPrix(float $prix): string
{
    return number_format($prix, 2, ',', ' ') . ' EUR';
}

function initiale(string $nom): string
{
    return mb_strtoupper(mb_substr($nom, 0, 1, 'UTF-8'), 'UTF-8');
}

function stockClasse(int $stock): string
{
    return $stock <= 10 ? 'stock-indicateur stock-faible' : 'stock-indicateur';
}

function stockLibelle(int $stock): string
{
    return $stock <= 10 ? 'Stock limite (' . $stock . ')' : 'En stock (' . $stock . ')';
}

function imageProduitParNom(string $nomProduit): ?string
{
    $imagesProduits = [
        'bleu intense' => 'images/bleu_intense.png',
        'rose élégante' => 'images/rose_elegante.png',
        'citrus energy' => 'images/citrus_energy.png',
        'nuit mystérieuse' => 'images/nuit_mysterieuse.png',
        'océan sport' => 'images/ocean_sport.png',
        'vanilla dream' => 'images/vanilla_dream.png'
    ];

    $nomCle = mb_strtolower(trim($nomProduit), 'UTF-8');
    return $imagesProduits[$nomCle] ?? null;
}

function extraitDescription(string $description, int $max = 95): string
{
    $description = trim($description);

    if (mb_strlen($description, 'UTF-8') <= $max) {
        return $description;
    }

    return rtrim(mb_substr($description, 0, $max - 1, 'UTF-8')) . '...';
}

function profilOlfactif(array $produit): array
{
    $profils = [
        'bleu intense' => [
            'top' => 'Menthe fraiche, lavande',
            'heart' => 'Bois aromatiques, epices douces',
            'base' => 'Ambre, cedre, feve tonka'
        ],
        'rose élégante' => [
            'top' => 'Bergamote, petales roses',
            'heart' => 'Rose veloutee, pivoine',
            'base' => 'Musc blanc, bois tendres'
        ],
        'citrus energy' => [
            'top' => 'Citron, mandarine, pamplemousse',
            'heart' => 'Neroli, gingembre leger',
            'base' => 'Musc, bois clairs'
        ],
        'nuit mystérieuse' => [
            'top' => 'Poivre noir, cardamome',
            'heart' => 'Encens, iris fume',
            'base' => 'Patchouli, cuir, ambre'
        ],
        'océan sport' => [
            'top' => 'Agrumes marins, menthe',
            'heart' => 'Lavande, sauge, notes aquatiques',
            'base' => 'Vetiver, mousse, musc'
        ],
        'vanilla dream' => [
            'top' => 'Amande douce, sucre blond',
            'heart' => 'Vanille cremeuse, fleur d oranger',
            'base' => 'Santal, praline, musc'
        ]
    ];

    $nomCle = mb_strtolower(trim($produit['nom_produit']), 'UTF-8');

    return $profils[$nomCle] ?? [
        'top' => $produit['marque_produit'],
        'heart' => $produit['categorie_produit'],
        'base' => $produit['concentration_parfum']
    ];
}
