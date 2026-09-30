<?php
    // Requête sql

    $query = "SELECT
                e.id,
                e.nom_ferme,
                e.province,
                e.philosophie
            FROM
                eleveur AS e
            ORDER BY
                nom_ferme ASC";

$statement = $pdo->query($query);
$eleveurs = $statement->fetchAll();
?>

<!-- Template -->
 <h1>Nos bergeries partenaires</h1>
 <p>Découvrez nos partenaires et leur philosophie</p>

 <table>
    <thead>
        <tr>
            <th>Nom de la ferme</th>
            <th>Province</th>
            <th>Philosophie</th>
            <th>Action</th>
        </tr>    
    </thead>
    <tbody>
        <?php foreach($eleveurs as $eleveur) : ?>
            <tr>
                <td><?= htmlspecialchars($eleveur['nom_ferme']) ?></td>
                <td><?= htmlspecialchars($eleveur['province']) ?></td>
                <td><?= htmlspecialchars($eleveur['philosophie']) ?></td>
                <td><a href="?page=eleveur-details&amp;id=<?= $eleveur['id'] ?>">Voir ses laines -></a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
 </table>