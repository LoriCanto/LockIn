<?php
require 'auth.php';
require 'config.php';
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
</head>

<body>
    <div id="div_contenitore">
        <h5>Sei loggato come: <?= htmlspecialchars($_SESSION['user_code']); ?> <a href="#" onclick="inviaPost('userManager.php', {action: 'logOut'}); return false;">Log Out</a></h5>
       
        <h1>Personal Page</h1>
        <?php
        // Controllo se l'utente è in coda
        $ret = presenzaCoda($pdo, $_SESSION['user_id']);
        if ($ret) {
            echo "<div id=GESTIONE_CODA> ";
            echo "<h3>Data prenotazione: " . $ret['data_prenotazione'] . "</h3>";
            echo "<h3>Posizione nella coda: " . $ret['posizione'] . "</h3>";
        ?>
            <div id=" queueing_section">
                <form action="queueManager.php" method="POST">
                    <input type="hidden" name="userID" value="<?= $ret['utente'] ?>">
                    <input type="hidden" name="action" value="remove">
                    <button id="logOut_button" type="submit">Toglimi dalla coda</button>
                </form>
            </div>
        <?php
            echo "</div> ";
        }
        // Controllo se l'utente ha un locker assegnato
        $ret = presenzaLocker($pdo, $_SESSION['user_id']);
        if ($ret) {
            echo "<div id=GESTIONE_LOCKER > ";
        ?>
            <h2>Il tuo Armadietto:</h2>
            <div style="display: flex; flex-direction: row;">
            <img src="images/<?php echo $ret['tipo']; ?>.png" alt="Locker Image" width="100" height="auto">
            <div id="locker_details" style="display: flex; flex-direction: column;">
                <label> Codice: <?= $ret['codice'] ?></label>

                <label> Tipo: <?= $ret['tipo'] ?></label>
                <label> Gruppo: <?= $ret['gruppo'] ?></label>
                <label> Data prenotazione: <?= $ret['data_prenotazione'] ?></label>
                 <form action="lockerManager.php" method="POST">
                <input type="hidden" name="lockerID" value="<?= $ret['id'] ?>">
                <input type="hidden" name="action" value="unlock">
                <button id="logOut_button" type="submit">Annulla questa prenotazione</button>
            </form>
            </div>
            </div>
           
        <?php
            echo "</div> ";
        } ?>
</body>

</html>
<?php
function presenzaCoda($pdo, $userID)
{
    $stmt = $pdo->prepare("SELECT *, (SELECT COUNT(*) FROM queue AS q2 WHERE q2.id <= q1.id) AS posizione FROM queue AS q1 WHERE q1.utente = ?");
    $stmt->execute([$userID]);
    $ret = $stmt->fetch(PDO::FETCH_ASSOC);
    return $ret;
}

function presenzaLocker($pdo, $userID)
{
    $stmt = $pdo->prepare("SELECT id,codice, data_prenotazione, tipo, gruppo FROM locker WHERE utente = ?");
    $stmt->execute([$userID]);
    $ret = $stmt->fetch(PDO::FETCH_ASSOC);
    return $ret;
}

?>