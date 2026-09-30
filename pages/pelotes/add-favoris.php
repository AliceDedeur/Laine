<?php

$clientId = $_SESSION['user']['id'];
$peloteId = $_GET['id'] ?? null;

if ($peloteId && is_numeric($peloteId)) {
    try {
        $sql = "INSERT INTO favori (client_id, pelote_id) VALUES (?, ?)";
        $statement = $pdo->prepare($sql);
        $statement->execute([$clientId, (int)$peloteId]);

    } catch (PDOException $e) {
    die("Erreur SQL lors de l'ajout : " . $e->getMessage());
}

}
header("Location: index.php?page=pelotes");
exit();
?>