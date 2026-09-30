<?php

// Informations de connexion à la base de données
$source = "sqlsrv";
$host = "localhost"; 
$dbname = "laine_pdo";

$dsn = "$source:Server=$host;Database=$dbname;TrustServerCertificate=true";
$user = "laine_user";
$pass = "Test1234=";

// 
// Demander à php de lever une exception dès qu'un script échoue. Par défaut, pdo peut échouer en silence. Ceci fait en sorte que php arrête le script et nous dise ce qui ne va pas (1° ligne). Renvoi les données sous forme de tableaux associatifs / évite le comportement par défaut PDO::FETCH_BOTH qui renverrai les données en double, index + nom / permet d'écrire des boucle foreach plus simples
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

// Tentative de connexion
try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  // Si la connexion échoue, le script s'arrête et affiche l'erreur
  die("Erreur de connexion au site Laine : " . $e->getMessage());
}

    
 