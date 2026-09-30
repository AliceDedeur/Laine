<?php 

session_start(); // Démarre la session

    // Gestion des routes

$routes = [

  '' => [
    'file' => 'pages/home.php',
    'title' => 'Accueil',
  ],

  // Gestion catalogue/pelotes

  'pelotes' => [
    'file' => 'pages/pelotes/read.php',   
    'title' => 'Notre catalogue de pelotes',
  ],

  'pelote-details' => [
    'file' => 'pages/pelotes/details.php',
    'title' => 'Description: traçabilité et fiche technique',
  ],

  'pelote-create' => [
    'file' => 'pages/pelotes/create.php',
    'title' => 'Ajouter une nouvelle pelote',
    'role' => ['admin'],
  ],

  'pelote-delete' => [
    'file' => 'pages/pelotes/delete.php',
    'title' => 'Supprimer une pelote',
    'role' => ['admin'],
  ],

  'pelote-update' => [
    'file' => 'pages/pelotes/update.php',
    'title' => 'Modifier une pelote',
    'role' => ['admin'],
  ],

  'pelote-favoris' => [
    'file' => 'pages/pelotes/favoris.php',
    'title' => 'Ajouter aux favoris',
    'role' => ['user', 'admin'],
  ],

  'pelote-add-favoris' => [
    'file' => 'pages/pelotes/add-favoris.php',
    'title' => 'Traitement favori',
    'role' => ['user', 'admin'],
],

  // Gestion éleveurs

  'eleveurs' => [
    'file' => 'pages/eleveurs/read.php',   
    'title' => 'Nos bergeries partenaires',
  ],

    'eleveur-details' => [
    'file' => 'pages/eleveurs/details.php',
    'title' => 'Découvrir la ferme',
  ],

  // Gestion authentification

  'register' => [
    'file' => 'pages/auth/register.php',
    'title' => 'Créer un compte',
  ],

  'login' => [
    'file' => 'pages/auth/login.php',
    'title' => 'Se connecter',
  ],

  'logout' => [
    'file' => 'pages/auth/logout.php',
    'title' => 'Se déconnecter',
  ],
];

// Gestion de la page introuvale
// quand le user fait une requête par url ($_GET), cette requête (page), si elle existe, est stockée dans la variable $page; si elle n'existe pas, le user sera renvoyé sur la page home (selon la route '' =>['file' => 'pages/home.php',...], Si la page existe, elle est donc stockée dans la variable $page, qui vérifie, alors, la route d'accès à cette page. La variable route contiendra le résultat de la recherche $routes[$page]. Si cette route n'existe pas, php renverra une page null que l'on détermine dans le passage juste après
$page = $_GET['page'] ?? '';
$route = $routes[$page] ?? null;

// Gestion des pages null
if($route === null) {
    $route = [
        'file' => 'pages/errors/not-found.php',
        'title' => '404 not found',
    ];
};

// Contrôle des rôles user/admin
// Rôles autorisés pour cette page (null = page publique)
$requiredRoles = $route['role'] ?? null;
if ($requiredRoles !== null) {
  
// Si pas connecté, retour au login (nouvelle requête)
    if(!isset($_SESSION['user'])) {
        header("Location: index.php?page=login");
        exit;
    }
// Connecté mais pas de rôleautorisé: on remplace la page demandée par la page d'erreur (même adresse dans la barre)
    if(!in_array($_SESSION['user']['role'], $requiredRoles)) {
        $route = [
            'file' => 'pages/errors/forbidden.php',
            'title' => '403 forbidden', 
        ];
    }
}

// On extrait les deux informations de la route validée dans deux variables
$file = $route['file'];
$title = $route['title'];

// php ouvre la connexion PDO avec la DB ce qui permet au fichier $file d'avoir accès à la variable $pdo, définie dans database.php, pour faire les requêtes sql (charger les infos des tables dans la DB)
require_once 'config/database.php';

// Mémoire tampon: 1. tout le code html doit être mit dans le cache momentanément. 2. php lit et exécute le contenu du fichier et le garde tjr dans le cache. 3. tout ce qui a été stocké dans le cache passe dans la variable $content
ob_start();
require_once $file;
$content = ob_get_clean();

// Assemblage final: header + $content (avec les infos passée du cache à la variable) + footer
require_once 'partials/header.php';
echo $content;
require_once 'partials/footer.php';

?>