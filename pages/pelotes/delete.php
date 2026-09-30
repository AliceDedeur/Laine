<?php

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
  http_response_code(405);
  echo "<h1>Action non autorisée.</h1>";
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
  $sql = "DELETE FROM pelote WHERE id = ?";
  $statement = $pdo->prepare($sql);
  $statement->execute([$id]);
}

header("Location: index.php?page=pelotes");
?>