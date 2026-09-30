<?php

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $eleveur = false;
    $pelotes = false;

    if($id !== false && $id !== null){
        $sql = "SELECT
                    e.id,
                    e.nom_ferme,
                    e.province,
                    e.philosophie
                FROM
                    eleveur AS e
                WHERE
                    e.id = ?";

        $statement = $pdo->prepare($sql);
        $statement->execute([$id]);

        $eleveur = $statement->fetch();

        $sql = "SELECT
                    p.id,
                    p.nom_commercial,
                    p.race_mouton,
                    p.description
                FROM
                    pelote p
                WHERE
                    p.eleveur_id = ?";

        $statement = $pdo->prepare($sql);
        $statement->execute([$id]);

        $pelotes = $statement->fetchAll();
    }

?>

<?php if(!$eleveur) : ?>

    <h1>Eleveur introuvable</h1>

<?php else : ?>

    <h1><?= htmlspecialchars($eleveur['nom_ferme']) ?> (<?= htmlspecialchars($eleveur['province']) ?>)</h1>
    <p><?= htmlspecialchars($eleveur['philosophie']) ?></p>

    <ul>
        <?php foreach($pelotes as $pelote) : ?>
        <li>
            <a href="?page=pelote-details&amp;id=<?= $pelote['id'] ?>"></a>
            <?= htmlspecialchars($pelote['nom_commercial']) ?>
        </li>
        <?php endforeach ?>
    </ul>

<?php endif ?>