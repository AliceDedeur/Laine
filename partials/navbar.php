<nav>
    <ul>
        <li><a href="index.php">Accueil</a></li>
        <li><a href="index.php?page=pelotes">Liste des pelotes</a></li>
        <li><a href="index.php?page=eleveurs">Liste des éleveurs</a></li>
    </ul>
    <ul>
        <?php if (!isset($_SESSION['user'])) : ?>
            <li><a href="index.php?page=login">Se connecter</a></li>
            <li><a href="index.php?page=register">S'inscrire</a></li>
        <?php else : ?>
            <li>Connecté : <?= $_SESSION['user']['email'] ?> (<?= $_SESSION['user']['role'] ?>)</li>
            <li><a href="index.php?page=logout">Se déconnecter</a></li>
        <?php endif; ?>
     </ul>
 </nav>