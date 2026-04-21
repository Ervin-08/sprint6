function mettreAJourCompteur(total) {
    const compteur = document.getElementById('compteur');
    if (!compteur) {
        return;
    }

    compteur.textContent = total;
    compteur.classList.add('panier-badge-pulse');
    window.setTimeout(function () {
        compteur.classList.remove('panier-badge-pulse');
    }, 450);
}

function afficherFeedback(succes, message) {
    const zone = document.getElementById('ajout-feedback');
    if (!zone) {
        return;
    }

    zone.className = 'ajout-feedback ' + (succes ? 'ajout-feedback--succes' : 'ajout-feedback--erreur');
    zone.textContent = '';

    const texte = document.createElement('span');
    texte.textContent = (succes ? 'Succes : ' : 'Erreur : ') + message;
    zone.appendChild(texte);

    if (succes) {
        const lien = document.createElement('a');
        lien.href = 'basket.php';
        lien.className = 'lien-panier-feedback';
        lien.textContent = 'Voir mon panier';
        zone.appendChild(lien);
    }
}

function ajouterAuPanier(idProduit, quantite) {
    const btn = document.querySelector('#form-ajout-panier button[type="submit"]');

    if (!btn) {
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Ajout en cours...';

    axios.postForm('add_to_cart.php', {
        product_id: idProduit,
        quantite: quantite,
    })
        .then(function (response) {
            mettreAJourCompteur(response.data);
            afficherFeedback(true, 'Le produit a bien ete ajoute au panier.');
        })
        .catch(function (error) {
            const message = error.response ? error.response.data : 'Une erreur est survenue.';
            afficherFeedback(false, message);
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = 'Ajouter au panier';
        });
}

function initFormAjoutPanier() {
    const form = document.getElementById('form-ajout-panier');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const idProduit = parseInt(form.dataset.idProduit, 10);
        const quantite = parseInt(form.querySelector('#quantite').value, 10);

        ajouterAuPanier(idProduit, quantite);
    });
}

document.addEventListener('DOMContentLoaded', initFormAjoutPanier);
