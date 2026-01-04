<?php
require 'auth.php';
require 'config.php';

$errorMsg = $_SESSION['LockerBookingError'];

// parametri in post
$posizione = $_SESSION['posizione'];
$tipo = $_SESSION['tipo'];

// prendo pos
$stmtPos = $pdo->query("SELECT DISTINCT posizione FROM locker ORDER BY posizione");
$pos = $stmtPos->fetchAll(PDO::FETCH_COLUMN);

// prendo tipi di armadietto
$stmtTipo = $pdo->query("SELECT DISTINCT tipo FROM locker ORDER BY tipo");
$tip = $stmtTipo->fetchAll(PDO::FETCH_COLUMN);

// se cambio posizione prendo i gruppi di quella posizione
if ($posizione) {
    $stmtGruppi = $pdo->prepare("SELECT DISTINCT gruppo FROM locker WHERE posizione = ? and tipo = ? ORDER BY gruppo");
    $stmtGruppi->execute([$posizione, $tipo]);
    $gruppi = $stmtGruppi->fetchAll(PDO::FETCH_COLUMN);
} else {
    $gruppi = [];
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>LockIn - Prenotazione</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/choosingLocker.css">
    <link rel="stylesheet" href="assets/css/LockInStyle.css">
</head>

<body>
    <h5>Sei loggato come: <?= htmlspecialchars($_SESSION['user_code']); ?> <a href="#" onclick="inviaPost('userManager.php', {action: 'logOut'}); return false;">Log Out</a></h5>

    <div id="main-layout">
        <aside id="menu">
            <h3>Piani</h3>
            <div class="menu-option">
                <form method="POST" action="lockerDataFilter.php">
                    <!-- <select name="tipo" id="tipoLocker" onchange="this.form.submit()">
                        <?php foreach ($tip as $lTipo) { ?>
                            <option value="<?= $lTipo ?>" <?= ($tipo == $lTipo) ? 'selected' : '' ?>>
                                <?= $lTipo ?>
                            </option>
                        <?php } ?>
                    </select> -->
                     <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                    <label for="pianoLocker">Scegli posizione:</label>
                    <select name="posizione" id="posizioneLocker" onchange="this.form.submit()">
                        <?php foreach ($pos as $lPos) { ?>
                            <option value="<?= $lPos ?>" <?= ($posizione == $lPos) ? 'selected' : '' ?>>
                                <?= $lPos ?>
                            </option>
                        <?php } ?>
                    </select>
                </form>
                <button onclick="location.href='lockerRoom.php'">Cambia dimensione</button>
            </div>
        </aside>

        <main id="locker-content">
            <h1>Scegli il tuo armadietto - <?= htmlspecialchars($posizione) ?></h1>
            <?php if ($errorMsg): ?>
                <div class="error-message">
                    <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>
            <?php if ($posizione): ?>
                <?php foreach ($gruppi as $nomeGruppo): ?>
                    <div class="gruppo-container">
                        <h2>Gruppo <?php echo $nomeGruppo; ?></h2>
                        <div class="grid-5x5">
                            <?php
                            //filrto  anche la posizione
                            $stmtLocker = $pdo->prepare("SELECT id, codice, utente,tipo FROM locker WHERE gruppo = ? AND posizione = ? LIMIT 25");
                            $stmtLocker->execute([$nomeGruppo, $posizione]);
                            $lockers = $stmtLocker->fetchAll();

                            foreach ($lockers as $locker):
                                $isOccupato = !empty($locker['utente']);
                            ?>
                                <?php if (!$isOccupato): ?>
                                    <form action="lockerManager.php" method="post">
                                        <input type="hidden" name="lockerID" value="<?php echo $locker['id']; ?>">
                                        <input type="hidden" name="action" value="lock">
                                        <input type="hidden" name="userID" value="<?php echo $_SESSION['user_id']; ?>">
                                        <div class="locker-box libero" onclick="this.parentNode.submit();">
                                            <img src="assets/images/<?php echo $locker['tipo']; ?>.png" alt="Armadietto" class="locker-img">
                                            <span class="locker-code"><?php echo $locker['codice']; ?></span>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="locker-box occupato">
                                        <img src="assets/images/<?php echo $locker['tipo']; ?>.png" alt="Armadietto" class="locker-img">
                                        <span class="locker-code"><?php echo $locker['codice']; ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>