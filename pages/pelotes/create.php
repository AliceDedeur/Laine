<?php
// Fonction servant à récupérer et sécuriser une variable envoyée dans l'url tout en vérifiant qu'il s'agit bien d'un nombre entier
// INPUT_GET demande à php de chercher la variable dans l'url ('id'). FILTER_VALIDATE_INT vérifie que c'est un nombre entier
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Pré-remplir le select des éleveurs
$query = "SELECT 
            id,
            nom_ferme 
        FROM eleveur 
        ORDER BY nom_ferme";

$eleveurs = $pdo->query($query)->fetchAll();

// Pour préparer le stokage de données d'un formulaire. Créer un tableau associatif qui initialise des clés (vides) qui recevront les valeurs du formulaire remplies par le user
$values = [
  'pelote-nom' => '',
  'pelote-race' => '',
  'pelote-description' => '',
  'pelote-conseil' => '',
  'pelote-stock' => '0',
  'pelote-prix' => '0',
  'pelote-eleveur' => '',
];

// créer un tableau errors de sorte à stocker les erreurs eventuelles pour pouvoir les rendre proprement plus loin dans le code
$errors = [];

// Gérer le formulaire
// Cette ligne vérifie si l'utilisateur à cliquer sur le boutton ajouter (qui utilise la méthode post)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Mapping de données: boucle foereach pour chaque donnée stockée dans le tableau des clés, définies plus haut, et qui a utilisé la méthode post
  // POST est un tableau associatif qui contient tout ce que le user à remplis dans le formulaire. 
  // array_keys($_POST) extrait le nom des clés
  // foreach (array_keys($_POST) as $field): démarre une boucle et la variable $fields, à chaque tour, va prendre un champ (clé) à la fois
  // ($_POST[$field]): va chercher la valeur entrée par le user de la clé en cours
  // trim() enlève les espaces vides inutiles. ?? '' Si un champ est manquant ou défectueux, php mettra une string vide par défaut sans afficher d'erreur
  foreach (array_keys($_POST) as $field) {
    $values[$field] = trim($_POST[$field]) ?? '';
  }

  // Validation des champs

  if ($values['pelote-nom'] === '') {
    $errors["pelote-nom"] = "Le nom est obligatoire";
  } else if (strlen($values['pelote-nom']) > 200) {
    $errors["pelote-nom"] = "Le nom ne peut pas dépasser 200 caractères.";
  }

  if ($values['pelote-race'] === '') {
    $errors["pelote-race"] = "La race est obligatoire";
  } else if (strlen($values['pelote-race']) > 200) {
    $errors["pelote-race"] = "La race ne peut pas dépasser 200 caractères.";
  }

  if ($values['pelote-description'] === '') {
    $errors["pelote-description"] = "La description est obligatoire";
  } else if (strlen($values['pelote-description']) > 500) {
    $errors["pelote-description"] = "La description ne peut pas dépasser 500 caractères.";
  }

  if ($values['pelote-conseil'] === '') {
    $errors["pelote-conseil"] = "Le conseil est obligatoire";
  } else if (strlen($values['pelote-conseil']) > 500) {
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
  // array_column($eleveurs, 'id'): fonction qui parcours le tableau éleveur et isole les id
  // array_map('intval': fonction qui permet d'appliquer une fonction, ici intval qui convertit des string en integer
  $eleveurId = array_map('intval', array_column($eleveurs, 'id'));
  if ($values['pelote-eleveur'] === '') {
    $errors["pelote-eleveur"] = "L'éleveur est obligatoire.";
  } else if (!in_array($values['pelote-eleveur'], $eleveurId)) {
    $errors['pelote-eleveur'] = "L'éleveur n'existe pas.";
  }

  // Insertion de la pelote dans la DB si pas d'erreur

  if (!$errors) {
  // S'il n'y a pas d'erreurs, on cherche (try) à insèrer les valeurs entrée par le user en fonction des clé définies plus haut.
  // Ces valeurs sont masquées dans un premier temps par sécurité (?, ?, ? ...). Elles seront lancées au moment d'executer, après le prepare()
    try {
      $sql = "
      INSERT INTO pelote (nom_commercial, race_mouton, description, conseil_lavage, quantite_stock, prix_unitaire, eleveur_id)
      VALUES (?, ?, ?, ?, ?, ?, ?)
      ";
    // requête sécurisée prepare et execute
    // $pdo (définie dans database.php) prépare la variable $sql. $pdo->prepare($sql): envois la structure de la requête au serveur sql, sans données
    // Sql analyse et renvoi un objet de déclaration stocké sans $statement
    // C'est au moment du execute() que les données réelles sont envoyées
      $statement = $pdo->prepare($sql);
      $statement->execute([
        $values['pelote-nom'],
        $values['pelote-race'],
        $values['pelote-description'],
        $values['pelote-conseil'],
        $values['pelote-stock'] !== '' ? (int) $values['pelote-stock'] : null, // opérateur ternaire: est ce que la valeur de la clé 'pelote-stock' n'est pas vide? true= php prend la valeur entrée; false= php la considère comme null
        $values['pelote-prix'] !== '' ? (float) $values['pelote-prix'] : null,
        (int) $values['pelote-eleveur'],
      ]);

      $newPeloteId = $pdo->lastInsertId(); // demande à la DB quel est le dernier id créé

      header('Location: index.php?page=pelote-details&id=' . $newPeloteId); // redirige vers ce dernier id créé par la DB
    } catch (PDOException $e) {
      $errors["pelote-nom"] = "Le nom est déjà utilisé.";
    }
  }
}
?>

<h1>Ajout de la pelote</h1>

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
    <label for="pelote-stock">Stock:</label>
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

  <button>Ajouter la pelote</button>

  <p>* champ obligatoire</p>
</form>