<?php
// Qui la parte di inserimento nel DB degli utenti 
// 1. Connesione a MYSQL + gestione degli eventuali errori 
// 2. Recuperiamo i dati invitati in POST dall'utente 
// 3. Costruriamo la query SQL 
// INSERT INTO utenti (nome, email) VALUES ('Mario Rossi', 'mario.rossi@example.com'); 
/// Eseguiamo la query e controlliamo il risultato 
// A valle del form visuallizzare gli utenti presenti nella tabella
//ALTER TABLE utenti AUTO_INCREMENT = 1; to reset it on the database if you deleted some
$servername = 'db';         
$username   = 'myuser';       
$password   = 'mypassword';   
$database   = 'myapp_db';    

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("<p style='color:red;'>❌ Connessione fallita: " . $conn->connect_error . "</p>");
}

$delete = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome  = trim($_POST["nome"] ?? '');
    $email = trim($_POST["email"] ?? '');

    if ($nome && $email) {
        $delete = false;
        $stmt = $conn->prepare("INSERT INTO utenti (nome, email) VALUES (?, ?)");
        $stmt->bind_param("ss", $nome, $email);

        if ($stmt->execute()) {
                echo "<p style='color:green;'>✅ Utente aggiunto con successo!</p>";
        } else {
            echo "<p style='color:red;'>❌ Errore durante l'inserimento: {$stmt->error}</p>";
        }

        $stmt->close();
    } else if($delete===false){
        echo "<p style='color:red;'>⚠️ Compila tutti i campi!</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['azione']) && $_POST['azione'] === 'elimina') {
    $delete = true;
    $id = intval($_POST["id"] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM utenti WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<p style='color:orange;'>🗑️ Utente eliminato!</p>";
        } else {
            echo "<p style='color:red;'>❌ Errore durante l'eliminazione: {$stmt->error}</p>";
        }
        $stmt->close();
    }
}

$result = $conn->query("SELECT id, nome, email FROM utenti ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti MySQL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container">
    <h2 class="text-center mb-4">Gestione Utenti (MySQL + PHP)</h2>

    <div class="card shadow p-4 mb-5">
        <h5 class="mb-3">Aggiungi nuovo utente</h5>
        <form method="POST" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="nome" placeholder="Nome & Cognome" class="form-control" required>
            </div>
            <div class="col-md-5">
                <input type="email" name="email" placeholder="Email" class="form-control" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Aggiungi</button>
            </div>
        </form>
    </div>

    <div class="card shadow p-4">
        <h5 class="mb-3">Utenti registrati</h5>
        <table class="table table-striped table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td> 
                                <td>" . htmlspecialchars($row['nome']) . "</td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                 <td>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='azione' value='elimina'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <button type='submit' class='btn btn-danger btn-sm'>Elimina</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Nessun utente trovato</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
