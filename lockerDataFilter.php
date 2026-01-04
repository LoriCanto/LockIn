<?php
require 'auth.php';
require 'config.php';

$prenotazione = $pdo->prepare("SELECT count(*) FROM (SELECT utente FROM locker UNION SELECT utente FROM queue) Presenza WHERE utente = ?");
$prenotazione->execute([$_SESSION['user_id']]);
$res = $prenotazione->fetchColumn();
if ($res > 0) {
    // L'utente ha già un armadietto assegnato
    header("Location: myLocker.php");
    exit();
}


$_SESSION['LockerBookingError'] = null;
$posizione = $_POST['posizione'];
$tipo = $_POST['tipo'];
$_SESSION['posizione'] = $posizione;
$_SESSION['tipo'] = $tipo;
header('Location: choosingLocker.php');


exit();
