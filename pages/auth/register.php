<?php
// variable pour stocker les erreurs
$errors = [];

// tableau associatif contenant les clés auxquelles seront rattachées les valeurs entrées par le user
$values = [
    'nom' => '',
    'prenom' => '',
    'telephone' => '',
    'email' => ''
];

// Si le formulaire est soumis en POST on donne aux clés la valeur entrée par le user mais si l'info n'est pas encore entrée par le user, on demande à php de laisser le champ vide
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $values['nom'] = trim($_POST['nom'] ?? '');
    $values['prenom'] = trim($_POST['prenom'] ?? '');
    $values['telephone'] = trim($_POST['telephone'] ?? '');
    $values['email'] = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmation = trim($_POST['confirmation'] ?? '');

    // 1. Validation du Nom
    if($values['nom'] === ''){
        $errors['nom'] = "Le nom est obligatoire";
    }

    // 2. Validation du Prénom
    if($values['prenom'] === ''){
        $errors['prenom'] = "Le prénom est obligatoire";
    }

    // 3. Validation de l'Email
    if($values['email'] === ''){
        $errors['email'] = "L'email est obligatoire";
    }
    else if(!filter_var($values['email'], FILTER_VALIDATE_EMAIL)){
        $errors['email'] = "Le format de l'email est incorrect";
    }
    else if(strlen($values['email']) > 180){
        $errors['email'] = "L'email ne peut excéder 180 caractères";
    }

    // 4. Validation du Mot de passe
    if($password === ''){
        $errors['password'] = "Le mot de passe est obligatoire";
    }
    else if(strlen($password) < 8){
        $errors['password'] = "Le mot de passe doit faire minimum 8 caractères";
    }
    else if(!preg_match('/[A-Z]/', $password)){
        $errors['password'] = "Le mot de passe doit contenir au moins une majuscule";
    }
    else if(!preg_match('/[^a-zA-Z0-9]/', $password)){
        $errors['password'] = "Le mot de passe doit contenir au moins un caractère spécial";
    }

    // 5. Validation de la Confirmation
    if($password !== $confirmation){
        $errors['confirmation'] = "Les deux mots de passe ne correspondent pas";
    }

    // Si la variable errors est vide -> insertion des données dans la DB
    if(!$errors){
        try{
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // requête SQL avec les 5 colonnes de ta table client
            $sql = "INSERT INTO client (nom, prenom, telephone, email, mot_de_passe)
                    VALUES (?, ?, ?, ?, ?)";

            $statement = $pdo->prepare($sql);
            
            $statement->execute([
                $values['nom'],
                $values['prenom'],
                $values['telephone'] === '' ? null : $values['telephone'], // comme le téléphone est null dans la DB, opération ternaire qui dit: la valeur téléphone est elle vide? si oui remplir avec null si nom remplir avec la valeur entrée par le user
                $values['email'],
                $password_hash
            ]);

            // :connexion à la session avec les valeurs du user
            $_SESSION['user'] = [
                'id' => $pdo->lastInsertId(),
                'email' => $values['email'],
                'role' => 'user'
            ];

            // redirection vers la page d'accueil
            header("Location: index.php");
            exit();

            // s'il y a une erreur, le catch l'attrape et la gère proprement (sans faire planter la requête) 
        } catch(PDOException $e) {
            // Affichez la vraie erreur pour comprendre ce qui ne va pas :
            die("Erreur SQL : " . $e->getMessage());
        }
    }
}
?>

<h1>S'inscrire</h1>

<form method="post">

    <div>
        <label for="nom">Nom : </label>
        <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($values['nom']) ?>">
        <?php if(isset($errors['nom'])) : ?>
            <span class="error"><?= $errors['nom'] ?></span>
        <?php endif ?>
    </div>

    <div>
        <label for="prenom">Prénom : </label>
        <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($values['prenom']) ?>">
        <?php if(isset($errors['prenom'])) : ?>
            <span class="error"><?= $errors['prenom'] ?></span>
        <?php endif ?>
    </div>

    <div>
        <label for="telephone">Téléphone (facultatif) : </label>
        <input type="text" name="telephone" id="telephone" value="<?= htmlspecialchars($values['telephone']) ?>">
    </div>

    <div>
        <label for="email">Email : </label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($values['email']) ?>">
        <?php if(isset($errors['email'])) : ?>
            <span class="error"><?= $errors['email'] ?></span>
        <?php endif ?>
    </div>

    <div>
        <label for="password">Mot de passe : </label>
        <input type="password" name="password" id="password">
        <?php if(isset($errors['password'])) : ?>
            <span class="error"><?= $errors['password'] ?></span>
        <?php endif ?>
    </div>

    <div>
        <label for="confirmation">Confirmation du mot de passe : </label>
        <input type="password" name="confirmation" id="confirmation">
        <?php if(isset($errors['confirmation'])) : ?>
            <span class="error"><?= $errors['confirmation'] ?></span>
        <?php endif ?>
    </div>

    <button type="submit">S'inscrire</button>

</form>
