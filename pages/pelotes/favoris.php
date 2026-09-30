<?php
// récupérer l'id du client
$clientId = $_SESSION['user']['id'];
// on récupère toutes les colonnes de la table pelote
$sql = "SELECT p.* FROM pelote p
        INNER JOIN favori f ON p.id = f.pelote_id
        WHERE f.client_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$clientId]);
$mesFavoris = $stmt->fetchAll();
?>

<h1>Mes Pelotes Favorites</h1>

<?php if (empty($mesFavoris)) : ?>
    <p>Vous n'avez pas encore de favoris.</p>
<?php else : ?>

    <div class="pelotes-container">
        <?php foreach($mesFavoris as $pelote) : ?>
            <div class="pelote-card">
                <h2><?= htmlspecialchars($pelote['nom_commercial']) ?></h2>
                <p><?= htmlspecialchars($pelote['description'] ?? '') ?></p>
                <span><?= htmlspecialchars($pelote['prix_unitaire']) ?> €</span>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>