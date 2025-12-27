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
<script>
function inviaPost(url, dati) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    for (const chiave in dati) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = chiave;
        input.value = dati[chiave];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>