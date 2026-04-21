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

function suppr(idProduit) {
    if (!confirm('Supprimer cet article du panier ?')) {
        return;
    }

    axios.postForm('supprimer_article.php', { product_id: idProduit })
        .then(function (response) {
            const ligne = document.querySelector('.tr-' + idProduit);
            if (ligne) {
                mettreAJourRecap(ligne);
                ligne.classList.add('basket-item-removing');
                window.setTimeout(function () {
                    ligne.remove();
                    verifierPanierVide();
                }, 180);
            }

            mettreAJourCompteur(response.data);
        })
        .catch(function (error) {
            const message = error.response ? error.response.data : 'Une erreur est survenue.';
            alert('Erreur : ' + message);
        });
}

function formaterPrix(valeur) {
    return valeur.toLocaleString('fr-BE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + ' EUR';
}

function mettreAJourRecap(ligne) {
    const totalArticles = document.getElementById('total-articles');
    const totalHtva = document.getElementById('total-htva');
    const totalTva = document.getElementById('total-tva');
    const totalTvac = document.getElementById('total-tvac');
    const page = document.querySelector('main[data-tva]');
    const basketCountLabel = document.getElementById('basket-count-label');

    if (!ligne || !totalArticles || !totalHtva || !totalTva || !totalTvac || !page) {
        return;
    }

    const quantiteRetiree = parseInt(ligne.dataset.quantite || '0', 10);
    const totalLigneRetire = parseFloat(ligne.dataset.totalLigne || '0');
    const tauxTva = parseFloat(page.dataset.tva || '0.21');

    const nouveauxArticles = Math.max(parseInt(totalArticles.textContent || '0', 10) - quantiteRetiree, 0);
    const nouveauHtva = Math.max(extraireMontant(totalHtva.textContent) - totalLigneRetire, 0);
    const nouvelleTva = nouveauHtva * tauxTva;
    const nouveauTvac = nouveauHtva + nouvelleTva;

    totalArticles.textContent = nouveauxArticles;
    totalHtva.textContent = formaterPrix(nouveauHtva);
    totalTva.textContent = formaterPrix(nouvelleTva);
    totalTvac.textContent = formaterPrix(nouveauTvac);

    if (basketCountLabel) {
        basketCountLabel.textContent = nouveauxArticles + ' article' + (nouveauxArticles > 1 ? 's' : '');
    }
}

function extraireMontant(texte) {
    return parseFloat((texte || '0').replace(/\s/g, '').replace('EUR', '').replace(',', '.')) || 0;
}

function verifierPanierVide() {
    const items = document.querySelectorAll('.basket-item');
    if (items.length > 0) {
        return;
    }

    const layout = document.querySelector('.basket-layout');
    if (layout) {
        layout.remove();
    }

    const main = document.querySelector('main');
    if (!main) {
        return;
    }

    const empty = document.createElement('section');
    empty.className = 'basket-empty-state';

    const title = document.createElement('h3');
    title.textContent = 'Votre panier est vide';

    const text = document.createElement('p');
    text.textContent = 'Explorez la collection pour ajouter un parfum a votre selection.';

    const link = document.createElement('a');
    link.href = 'produits.php';
    link.className = 'landing-btn landing-btn-primary';
    link.textContent = 'Voir les produits';

    empty.appendChild(title);
    empty.appendChild(text);
    empty.appendChild(link);
    main.appendChild(empty);
}
