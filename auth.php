<?php
// Inizio la sessione per recuperare l'ID dell'utente loggato
session_start();

// Controllo se l'utente è effettivamente loggato
// Se la sessione è vuota, lo rimando alla pagina di login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>