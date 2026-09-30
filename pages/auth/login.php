<?php

$errors = [];

$values = [ 'email' => '' ];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $values['email'] = trim($_POST['email'] ?? '');
  $password = trim($_POST["password"] ?? '');

  if ($values['email'] === '') {
    $errors['email'] =  "L'email est obligatoire.";
  }
  else if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Le format de l'email est incorrect.";
  }

  if ($password === '') {
    $errors['password'] = "Le mot de passe est obligatoire.";
  }

  if (!$errors) {

    $sql = "SELECT id, email, mot_de_passe, role FROM client WHERE email = ?";

    $statement = $pdo->prepare($sql);
    $statement->execute([$values['email']]);

    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
      $errors['global'] = "L'email et/ou mot de passe incorrect.";
    }
    else {
      $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $values['email'],
        'role' => $user['role']
      ];

      header("Location: index.php?page=pelotes");
      exit();
    }
  }
}
?>

<h1>Se connecter</h1>

<?php if (isset($errors['login'])) : ?>
    <div class="error-box" style="color: red; font-weight: bold; margin-bottom: 15px;">
        <?= $errors['login'] ?>
    </div>
<?php endif ?>

<form method="post">
    <div>
        <label for="email">Email : </label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($values['email']) ?>">
        <?php if (isset($errors['email'])) : ?>
            <span class="error" style="color: red;"><?= $errors['email'] ?></span>
        <?php endif ?>
    </div>

    <div>
        <label for="password">Mot de passe : </label>
        <input type="password" name="password" id="password">
        <?php if (isset($errors['password'])) : ?>
            <span class="error" style="color: red;"><?= $errors['password'] ?></span>
        <?php endif ?>
    </div>

    <button type="submit">Se connecter</button>
</form>
