<?php
require 'auth.php';
require 'config.php';

$resGrandi = $pdo->query("SELECT COUNT(*) FROM locker WHERE UCase(tipo) = 'GRANDE' AND (utente IS NULL OR utente = '')");
$numGrandi = $resGrandi->fetchColumn();

$resPiccoli = $pdo->query("SELECT COUNT(*) FROM locker WHERE UCASE(tipo) = 'PICCOLO' AND (utente IS NULL OR utente = '')");
$numPiccoli = $resPiccoli->fetchColumn();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" ;>
    <title>LockIn: prenota il tuo amradietto!</title>
    <meta name="viewport" content="width=device-width,initial-scale=    1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="LockInStyle.css">
    <link rel="stylesheet" href="lokerRoom_Style.css">
</head>

<body>
    <div id="div_contenitore">
        <h5>Sei loggato come: <?= htmlspecialchars($_SESSION['user_code']); ?></h5>
        <div id="queueing_section">
            <form action="queueManager.php" method="post">
            <h2>Tutti gli armadietti sono stati presi, mettiti in coda!</h2>
            <input type="hidden" name="userID" value="<?= $_SESSION['user_id'] ?>">
            <input type="hidden" name="action" value="insert">
            <button type="submit">Mettimi in coda</button>
            </form>
        </div>
        <h1>Scegli la dimensione che desideri</h1>
        <div id='griglia'>
            <div id='bigLock_container'>
                <div id='bigLock_button' onclick="window.location.href = 'chooseBigLocker.php';">
                    <img src="images/armadiettogrande.jpg">
                    <h2>Armadietto Grande</h2>
                    <h4> Disponibilità: <span id="bigLock_availability"><?= $numGrandi ?></span></label>
                </div>
            </div>
            <div id='smalLock_container'>
                <div id="smalLock_button">
                    <img src="images/armadiettopiccolo.jpg">
                    <h2>Armadietto Piccolo</h2>
                    <h4> Disponibilità: <span id="smallLock_availability"><?= $numPiccoli ?></span></h4>
                </div>
            </div>
        </div>
</body>