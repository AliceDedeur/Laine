<?php  
    // la fonction filter_input sert a sécuriser l'id. INPUT_GET regarde dans la barre d'adresse, id est le nom du paramètre recherché, FILTER_VALIDATE_INT vérifie que c'est bien un nombre entier (évite de pouvoir écrire du texte à la place)
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    // valeur de sécurité par défaut. Si ID correcte, la variable est écrasée par les vraies infos sinon elle reste sur false et pourra renvoyer le message d'erreur écrit dans le HTML
    $pelote = false;

    if($id !== false && $id !== null) {
        $sql = "SELECT p.id,
                       p.nom_commercial,
                       p.race_mouton,
                       p.description,
                       p.conseil_lavage,
                       p.quantite_stock,
                       p.prix_unitaire,
                       e.id AS eleveur_id,
                       e.nom_ferme AS eleveur_nom_ferme,
                       e.province AS eleveur_province,
                       e.philosophie AS eleveur_nationalite
                FROM pelote AS p
                     JOIN eleveur AS e ON p.eleveur_id = e.id
                WHERE p.id = ?"; // ? pour se prémunir du piratage par injection sql (requête préparée)

        $statement = $pdo->prepare($sql);
        $statement->execute([$id]);

        $pelote = $statement->fetch();

        // requête séparée pour les étapes de transformation et non une jointure géante: cela évite que les infos de la pelote soient répétées autant de fois qu'il y a d'étapes
        $sql = "SELECT  t.type_action,
                        t.nom_intervenant,
                        t.ordre_etape
                      FROM transformation AS t
                      WHERE pelote_id = ?
                      ORDER BY ordre_etape ASC";

        $statement = $pdo->prepare($sql);
        $statement->execute([$id]);

        $etapes = $statement->fetchAll();
    }
    
?>
<div class="container-details">
<?php if(!$pelote) : ?>

    <h1>Pelote introuvable</h1>
    <p>Aucune pelote ne correspond à l'id <?= $id ?>.</p>

    
<?php else : ?>

    <h1><?= htmlspecialchars($pelote['nom_commercial']) ?></h1>
    <h4>Race du mouton: <span><?= htmlspecialchars($pelote['race_mouton'] ?? 'inconnue') ?></span></h4>
    <p>Description: <span><?= htmlspecialchars($pelote['description'] ?? 'Aucune description') ?></span></p>
    <p>Conseil de lavage: <span><?= htmlspecialchars($pelote['conseil_lavage'] ?? '-') ?></span></p>       
    <p>Quantité en stock: <span><?= number_format($pelote['quantite_stock']) ?></span></p>             
    <p>Prix Unitaire: <span><?= number_format($pelote['prix_unitaire']) ?> euros</span></p>
    <p>Bergerie: 
        <a href="?page=eleveur-details&amp;id=<?= $pelote['eleveur_id'] ?>">
            <?= htmlspecialchars($pelote['eleveur_nom_ferme']) ?>
        </a>(<?= htmlspecialchars($pelote['eleveur_province']) ?>)
    </p>
    
<?php endif ?>

    <h3>Etape de fabrication de cette laine</h3>

    <ul>
        <?php foreach($etapes as $etape) : ?>
            <li>
                <?= htmlspecialchars($etape['ordre_etape']) ?>
                <?= htmlspecialchars($etape['type_action']) ?> :
                <?= htmlspecialchars($etape['nom_intervenant']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

    

