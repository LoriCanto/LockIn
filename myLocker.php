    <?php
    require 'auth.php';
    require 'config.php';
    ?>

    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>LockIn: prenota il tuo armadietto!</title>
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/LockInStyle.css">
        <link rel="icon" type="image/png" href="assets/images/favicon.ico">
        <script>
            let formDaInviare = null;

            // Funzione per mostrare la modale
            function apriModale(idForm, messaggio) {
                formDaInviare = document.getElementById(idForm);
                document.getElementById('modal-text').innerText = messaggio;
                document.getElementById('modal-overlay').style.display = 'flex';
            }

            // Funzione per nascondere la modale
            function chiudiModale() {
                document.getElementById('modal-overlay').style.display = 'none';
                formDaInviare = null;
            }

            // Gestione del click sul tasto conferma della modale
            window.onload = function() {
                document.getElementById('confirm-btn-action').onclick = function() {
                    if (formDaInviare) {
                        formDaInviare.submit();
                    }
                };

                // Chiude la modale se si clicca sullo sfondo
                document.getElementById('modal-overlay').onclick = function(event) {
                    if (event.target == this) {
                        chiudiModale();
                    }
                };
            };

            // Funzione per il logout
            function inviaPost(url, data) {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = url;
                for (var key in data) {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = key;
                    input.value = data[key];
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            }
        </script>
    </head>

    <body>
        <div id="modal-overlay">
            <div class="modal-content">
                <h3 id="modal-title">Richiesta di conferma</h3>
                <p id="modal-text">Sei sicuro di voler procedere?</p>
                <div class="modal-buttons">
                    <button class="btn-cancel" onclick="chiudiModale()">Annulla</button>
                    <button class="btn-confirm" id="confirm-btn-action">Conferma</button>
                </div>
            </div>
        </div>

        <div id="div_contenitore">
            <h5>Sei loggato come: <?= htmlspecialchars($_SESSION['user_code']); ?>
                <a href="#" onclick="inviaPost('userManager.php', {action: 'logOut'}); return false;">Log Out</a>
            </h5>

            <h1>Personal Page</h1>
            <div id="personal_page_content">
                <?php
                // Controllo se l'utente è in coda
                $retC = presenzaCoda($pdo, $_SESSION['user_id']);
                if ($retC) {
                    echo "<div id='GESTIONE_CODA'>";
                    echo "<h3>Data prenotazione: " . htmlspecialchars($retC['data_prenotazione']) . "</h3>";
                    echo "<h3>Posizione nella coda: " . htmlspecialchars($retC['posizione']) . "</h3>";
                ?>
                    <div id="queueing_section">
                        <form id="form-coda" action="queueManager.php" method="POST">
                            <input type="hidden" name="userID" value="<?= $retC['utente'] ?>">
                            <input type="hidden" name="action" value="remove">
                            <button id="logOut_button" type="button" onclick="apriModale('form-coda', 'Vuoi davvero uscire dalla coda di attesa?')">Toglimi dalla coda</button>
                        </form>
                    </div>
                <?php
                    echo "</div>";
                }

                // Controllo se l'utente ha un locker assegnato
                $retL = presenzaLocker($pdo, $_SESSION['user_id']);
                if ($retL) {
                    echo "<div id='GESTIONE_LOCKER'>";
                ?>
                    <h2>Il tuo Armadietto:</h2>
                    <div id="locker_container" ">
                        <img src=" assets/images/<?= htmlspecialchars($retL['tipo']); ?>.png" alt="Locker Image" width="100" height="auto">
                        <div id="locker_details" style="display: flex; flex-direction: column;">
                            <label> Codice: <?= htmlspecialchars($retL['codice']) ?></label>
                            <label> Piano: <?= htmlspecialchars($retL['posizione']) ?></label>
                            <label> Tipo: <?= htmlspecialchars($retL['tipo']) ?></label>
                            <label> Gruppo: <?= htmlspecialchars($retL['gruppo']) ?></label>
                            <label> Data prenotazione: <?= htmlspecialchars($retL['data_prenotazione']) ?></label>

                            <form id="form-locker" action="lockerManager.php" method="POST">
                                <input type="hidden" name="lockerID" value="<?= $retL['id'] ?>">
                                <input type="hidden" name="action" value="unlock">
                                <button id="logOut_button" type="button" onclick="apriModale('form-locker', 'Sei sicuro di voler rilasciare questo armadietto? Non potrai tornare indietro.')">Annulla questa prenotazione</button>
                            </form>
                        </div>
                    </div>
                <?php
                    echo "</div>";
                } ?>
            </div>
        </div>
    </body>

    </html>

    <?php
    function presenzaCoda($pdo, $userID)
    {
        $stmt = $pdo->prepare("SELECT *, (SELECT COUNT(*) FROM queue AS q2 WHERE q2.id <= q1.id) AS posizione FROM queue AS q1 WHERE q1.utente = ?");
        $stmt->execute([$userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function presenzaLocker($pdo, $userID)
    {
        $stmt = $pdo->prepare("SELECT id, codice, data_prenotazione, tipo,posizione, gruppo FROM locker WHERE utente = ?");
        $stmt->execute([$userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>