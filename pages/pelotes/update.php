<?php
// pour vérifier que l'id de l'url est bien un entier
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Pré-remplir le select des éleveurs
$query = "SELECT id, nom_ferme FROM eleveur ORDER BY nom_ferme";
$eleveurs = $pdo->query($query)->fetchAll();

$statement = $pdo->prepare("SELECT id, nom_commercial, race_mouton, description, conseil_lavage, quantite_stock, prix_unitaire, eleveur_id FROM pelote WHERE id = ?");
$statement->execute([$id]);
$pelote = $statement->fetch();

if (!$pelote) {
  header("Location: index.php?page=pelotes");
  exit;
}

$values = [
  'pelote-nom' => $pelote['nom_commercial'],
  'pelote-race' => $pelote['race_mouton'],
  'pelote-description' => $pelote['description'],
  'pelote-conseil' => $pelote['conseil_lavage'],
  'pelote-stock' => $pelote['quantite_stock'],
  'pelote-prix' => $pelote['prix_unitaire'],
  'pelote-eleveur' => $pelote['eleveur_id'],
];

$errors = [];

// Gérer le formulaire

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Mapping de données
  foreach (array_keys($_POST) as $field) {
    $values[$field] = trim($_POST[$field]) ?? '';
  }

  // Validation des champs

  if ($values['pelote-nom'] === '') {
    $errors["pelote-nom"] = "Le nom est obligatoire";
  } else if (strlen($values['pelote-nom']) > 100) {
    $errors["pelote-nom"] = "Le nom ne peut pas dépasser 200 caractères.";
  }

  if ($values['pelote-race'] === '') {
    $errors["pelote-race"] = "La race est obligatoire";
  } else if (strlen($values['pelote-race']) > 100) {
    $errors["pelote-race"] = "La race ne peut pas dépasser 200 caractères.";
  }

  if ($values['pelote-description'] === '') {
    $errors["pelote-description"] = "La description est obligatoire";
  } else if (strlen($values['pelote-description']) > 250) {
    $errors["pelote-description"] = "La description ne peut pas dépasser 500 caractères.";
  }

  if ($values['pelote-conseil'] === '') {
    $errors["pelote-conseil"] = "Le conseil est obligatoire";
  } else if (strlen($values['pelote-conseil']) > 250) {
    $errors["pelote-conseil"] = "Le conseil ne peut pas dépasser 500 caractères.";
  }

  if ($values["pelote-stock"] !== '') {
    $stock = filter_var($values["pelote-stock"], FILTER_VALIDATE_INT);
    if (!$stock || $stock < 0) {
      $errors["pelote-stock"] = "Le stock doit être positif.";
    }
  }

  if ($values["pelote-prix"] !== '') {
    $prix = filter_var($values["pelote-prix"], FILTER_VALIDATE_FLOAT);
    if (!$prix || $prix < 0) {
      $errors["pelote-prix"] = "Le prix doit être positif.";
    }
  }

  // 5. Id auteur
  $eleveurId = array_map('intval', array_column($eleveurs, 'id'));
  if ($values['pelote-eleveur'] === '') {
    $errors["pelote-eleveur"] = "L'éleveur est obligatoire.";
  } else if (!in_array($values['pelote-eleveur'], $eleveurId)) {
    $errors['pelote-eleveur'] = "L'éleveur n'existe pas.";
  }

  // Modification d'un livre dans la DB si pas d'erreur

  if (!$errors) {

    try {
      $sql = "
      UPDATE pelote
      SET
        nom_commercial = ?,
        race_mouton = ?,
        description = ?,
        conseil_lavage = ?,
        quantite_stock = ?,
        prix_unitaire = ?,
        eleveur_id = ?
      WHERE id = ?
      ";

      $statement = $pdo->prepare($sql);
      $statement->execute([
        $values['pelote-nom'],
        $values['pelote-race'],
        $values['pelote-description'],
        $values['pelote-conseil'],
        $values['pelote-stock'] !== '' ? (int) $values['pelote-stock'] : null,
        $values['pelote-prix'] !== '' ? (float) $values['pelote-prix'] : null,
        (int) $values['pelote-eleveur'],
        $id
      ]);

      header('Location: index.php?page=pelote-details&id=' . $id);
      exit;
    } catch (PDOException $e) {
      $errors["pelote-nom"] = "Le nom est déjà utilisé.";
    }
  }
}
?>

<h1>Modification d'une pelote</h1>

<form method="post">

  <div>
    <label for="pelote-nom">Nom: *</label>
    <input type="text" name="pelote-nom" id="pelote-nom" required value="<?= $values['pelote-nom'] ?>">
    <?php if (isset($errors['pelote-nom'])) : ?>
      <span class="error"><?= $errors['pelote-nom'] ?></span>
    <?php endif ?>
  </div>

  <div>
    <label for="pelote-race">Race du mouton:</label>
    <input type="text" name="pelote-race" id="pelote-race"  value="<?= $values['pelote-race'] ?>">
    <?php if (isset($errors['pelote-race'])) : ?>
      <span class="error"><?= $errors['pelote-race'] ?></span>
    <?php endif ?>
  </div>

  <div>
    <label for="pelote-description">Description:</label>
    <input type="text" name="pelote-description" id="pelote-description"  value="<?= $values['pelote-description'] ?>">
    <?php if (isset($errors['pelote-description'])) : ?>
      <span class="error"><?= $errors['pelote-description'] ?></span>
    <?php endif ?>
  </div>

  <div>
    <label for="pelote-conseil">Conseil de lavage:</label>
    <input type="text" name="pelote-conseil" id="pelote-conseil"  value="<?= $values['pelote-conseil'] ?>">
    <?php if (isset($errors['pelote-conseil'])) : ?>
      <span class="error"><?= $errors['pelote-conseil'] ?></span>
    <?php endif ?>
  </div>

  <div>
    <label for="pelote-eleveur">Eleveurs: *</label>
    <select name="pelote-eleveur" id="pelote-eleveur" required value="<?= $values['pelote-eleveur'] ?>">
      <option value=""> --- Sélectionner un éleveur --- </option>
      <?php foreach ($eleveurs as $eleveur) : ?>
        <option 
          value="<?= htmlspecialchars($eleveur["id"]) ?>"
          <?= $values["pelote-eleveur"] === $eleveur["id"] ? 'selected' : '' ?>
        >
          <?= htmlspecialchars($eleveur["nom_ferme"]) ?>
        </option>
      <?php endforeach ?>
    </select>
    <?php if (isset($errors['pelote-eleveur'])) : ?>
      <span class="error"><?= $errors['pelote-eleveur'] ?></span>
    <?php endif ?>
  </div>

  <div>
    <label for="pelote-stock">Prix:</label>
    <input type="number" min="0" step="1" name="pelote-stock" id="pelote-stock" value="<?= $values['pelote-stock'] ?>">
    <?php if (isset($errors['pelote-stock'])) : ?>
      <span class="error"><?= $errors['pelote-stock'] ?></span>
    <?php endif ?>
  </div>

  <div>
    <label for="pelote-prix">Prix:</label>
    <input type="number" min="0" step="0.001" name="pelote-prix" id="pelote-prix" value="<?= $values['pelote-prix'] ?>">
    <?php if (isset($errors['pelote-prix'])) : ?>
      <span class="error"><?= $errors['pelote-prix'] ?></span>
    <?php endif ?>
  </div>

  <button>Modifier la pelote</button>

  <p>* champ obligatoire</p>
</form>