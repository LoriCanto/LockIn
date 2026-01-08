<?php
require 'auth.php';
require 'config.php';

$errorMsg = $_SESSION['LockerBookingError'];

// parametri in sessione/post
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
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">
</head>

<body>
    <script>
        function mostraCaricamento() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.style.display = 'flex';
            }
        }

        function inviaPost(url, data) {
            mostraCaricamento();
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            for (const key in data) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            }
            document.body.appendChild(form);
            // Piccolo ritardo anche qui per sicurezza visiva
            setTimeout(() => {
                form.submit();
            }, 50);
        }
    </script>

    <div id="loading-overlay" style="display:none;">
        <img src="assets/images/caricamentoLucchetto.gif" alt="Caricamento..." width="100">
        <p>Caricamento...</p>
    </div>

    <div id="div_contenitore">
        <h5>Sei loggato come: <?= htmlspecialchars($_SESSION['user_code']); ?>
            <a href="#" onclick="inviaPost('userManager.php', {action: 'logOut'}); return false;">Log Out</a>
        </h5>

        <main id="locker-content">
            <h1>Scegli il tuo armadietto - <?= htmlspecialchars($posizione) ?></h1>

            <div class="header-booking">
                <button type="button" onclick="location.href='lockerRoom.php'">Cambia dimensione</button>

                <form id="formPosizione" method="POST" action="lockerDataFilter.php" class="form-posizione-inline">
                    <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                    <label for="posizioneLocker">Piano:</label>
                    <select name="posizione" id="posizioneLocker" onchange="mostraCaricamento(); setTimeout(() => { this.form.submit(); }, 50);">
                        <?php foreach ($pos as $lPos) { ?>
                            <option value="<?= $lPos ?>" <?= ($posizione == $lPos) ? 'selected' : '' ?>>
                                <?= $lPos ?>
                            </option>
                        <?php } ?>
                    </select>
                </form>
            </div>

            <?php if ($errorMsg): ?>
                <div class="error-message">
                    <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <?php if ($posizione): ?>
                <?php foreach ($gruppi as $nomeGruppo): ?>
                    <div class="gruppo-container">
                        <h2>Gruppo <?php echo htmlspecialchars($nomeGruppo); ?></h2>
                        <div class="grid-5x5">
                            <?php
                            $stmtLocker = $pdo->prepare("SELECT id, codice, utente, tipo FROM locker WHERE gruppo = ? AND posizione = ? AND tipo = ? LIMIT 25");
                            $stmtLocker->execute([$nomeGruppo, $posizione, $tipo]);
                            $lockers = $stmtLocker->fetchAll();

                            foreach ($lockers as $locker):
                                $isOccupato = !empty($locker['utente']);
                            ?>
                                <?php if (!$isOccupato): ?>
                                    <form action="lockerManager.php" method="post">
                                        <input type="hidden" name="lockerID" value="<?php echo $locker['id']; ?>">
                                        <input type="hidden" name="action" value="lock">
                                        <input type="hidden" name="userID" value="<?php echo $_SESSION['user_id']; ?>">

                                        <div class="locker-box libero" onclick="mostraCaricamento(); setTimeout(() => { this.parentNode.submit(); }, 100);">
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