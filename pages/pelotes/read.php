<?php
    // Requête sql avec jointure pour récupérer les pelottes depuis (FROM) la DB (table pelotes) et les afficher dans des cards

    $query = "SELECT
                p.id,
                p.nom_commercial,
                p.race_mouton,
                p.description,
                p.conseil_lavage,
                p.quantite_stock,
                p.prix_unitaire,
                e.nom_ferme AS eleveur_nom_ferme
            FROM 
                pelote p
            JOIN
                eleveur AS e ON p.eleveur_id = e.id"; // on relie l'enfant (pelote) au parent (eleveur) en faisant correspondre la FK avec la PK

// excécuter la requête et récupérer les données dans $statement avec la methode query() depuis l'objet pdo définit dans la database et appelé dans index (pas besoin de sécuriser l'url, pas de variable user, si non méthode prepare(), execute()) Ensuite avec fetchAll on demande à php d'extraire toutes les données récupérées dans statement et de les stocker sous forme de tableau dans la variable $pelotes
$statement = $pdo->query($query);
$pelotes = $statement->fetchAll();
?>

<!-- Template -->

<h1>Notre catalogue de laine MADE IN BELGIUM</h1>
<p class="subtitle">Découvrez nos pelotes artisanales et leur traçabilité</p>

<div class="pelotes-container">
    <!-- Pour chaque pelote de la table pelotes afficher les infos précisées ci dessous -->
    <?php foreach($pelotes as $pelote) : ?>
        <div class="pelote-card">
            <h2><?= htmlspecialchars($pelote['nom_commercial']) ?></h2> <!-- htmlspecialchars= sécurise l'affichage des données sur la page web. Convertit les caractères spéciaux en entité html sécurisées.Si un hacker introduit un script malveillant, celui ci sera transformé en caractères inoffensifs (ex: <script> n'apparaîtera plus sous cette forme et il ne pourra pas s'éxécuter-->
            <p class="description"><?= htmlspecialchars($pelote['description']) ?></p>
            <span class="prix"><?= htmlspecialchars($pelote['prix_unitaire']) ?> €</span>
            
            <div class="card-btn">
                <a href="?page=pelote-details&amp;id=<?= $pelote['id'] ?>" class="btn btn-primary">Origine de la pelote</a>
                <a href="index.php?page=pelote-favoris&amp;id=<?= $pelote['id'] ?>" class="btn btn-secondary">Ajouter aux favoris</a>
            </div>

            <!-- Partie admin: si la session du user a le rôle requis (admin), afficher les différents boutons ci dessous -->
            <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') : ?>
                <div class="card-admin">
                    <p style="text-align: center;">Administration :</p>
                    <div class="admin-btn">
                        <a href="index.php?page=pelote-update&amp;id=<?= $pelote['id'] ?>" class="btn">Modifier</a>
                        <a href="index.php?page=pelote-create" class="btn">Nouvelle</a>
                        <a href="index.php?page=pelote-delete&amp;id=<?= $pelote['id'] ?>" class="btn">Supprimer</a>               
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach ?>
</div>
