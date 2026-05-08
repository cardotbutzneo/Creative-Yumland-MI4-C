<?php 

require_once __DIR__."/../serveur.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION) || !isset($_SESSION["role"])) {
    header("Location: connexion.php?error=notconnected");
    exit;
}

$role = $_SESSION["role"];
$_SESSION["derniere-connexion"] = time();


$aujourdhui = date("Y-m-d");
$demain = date("Y-m-d",strtotime("+1 day"));

?>