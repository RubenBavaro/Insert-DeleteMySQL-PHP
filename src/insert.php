<?php
// ====================== CONNESSIONE DB ======================
$servername = 'db';
$username   = 'myuser';
$password   = 'mypassword';
$database   = 'myapp_db';

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("<p style='color:red;'>❌ Connessione fallita: " . $conn->connect_error . "</p>");
}

/* ============================================================
                        CREATE
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["azione"] === "aggiungi") {
    $nome  = trim($_POST["nome"]);
    $email = trim($_POST["email"]);

    if ($nome && $email) {
        $stmt = $conn->prepare("INSERT INTO utenti (nome, email) VALUES (?, ?)");
        $stmt->bind_param("ss", $nome, $email);
        $stmt->execute();
        $stmt->close();
        echo "<p style='color:green;'>Utente aggiunto!</p>";
    }
}

/* ============================================================
                        DELETE (singolo)
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["azione"] === "elimina") {
    $id = intval($_POST["id"]);

    $stmt = $conn->prepare("DELETE FROM utenti WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo "<p style='color:orange;'>Utente eliminato!</p>";
}

/* ============================================================
                        UPDATE
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["azione"] === "modifica") {

    $id    = intval($_POST["id"]);
    $nome  = trim($_POST["nome"]);
    $email = trim($_POST["email"]);

    if ($id > 0 && $nome && $email) {
        $stmt = $conn->prepare("UPDATE utenti SET nome=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $nome, $email, $id);
        $stmt->execute();
        $stmt->close();

        echo "<p style='color:blue;'>Utente aggiornato!</p>";
    }
}

/* ============================================================
                ELIMINA MULTIPLI
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["azione"] === "eliminaMultipli") {

    if (!empty($_POST["selected"])) {
        $ids = array_map("intval", $_POST["selected"]);
        $in  = implode(",", $ids);

        $conn->query("DELETE FROM utenti WHERE id IN ($in)");

        echo "<p style='color:red;'>Eliminati " . count($ids) . " utenti selezionati.</p>";
    }
}

/* ============================================================
                RESET AUTO_INCREMENT
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST["azione"] === "ResetID") {

    $conn->query("ALTER TABLE utenti AUTO_INCREMENT = 1;");

    echo "<p style='color:purple;'>AUTO_INCREMENT ripristinato.</p>";
}

$result = $conn->query("SELECT id, nome, email FROM utenti ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti - PHP + MYSQL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .edit-row { background: #fff4c2; }
    </style>
</head>
<body class="bg-light p-4">

<div class="container">

    <h2 class="text-center mb-4">Gestione Utenti (CRUD)</h2>


    <!-- ============================================================
                        FORM AGGIUNTA UTENTE
    ============================================================ -->
    <div class="card shadow p-4 mb-4">
        <h5 class="mb-3">Aggiungi nuovo utente</h5>

        <form method="POST" class="row g-3">
            <input type="hidden" name="azione" value="aggiungi">

            <div class="col-md-5">
                <input type="text" name="nome" class="form-control" placeholder="Nome & Cognome" required>
            </div>

            <div class="col-md-5">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">Aggiungi</button>
            </div>
        </form>
    </div>



    <!-- ============================================================
                        TABELLA UTENTI
    ============================================================ -->
    <div class="card shadow p-4">

        <h5 class="mb-3">Utenti registrati</h5>

        <!-- Reset ID -->
        <form method="POST" class="d-inline-block mb-2">
            <input type="hidden" name="azione" value="ResetID">
            <button class="btn btn-info btn-sm">Reset ID</button>
        </form>

        <!-- Elimina multipli -->
        <form method="POST" id="multiForm" class="d-inline-block mb-2">
            <input type="hidden" name="azione" value="eliminaMultipli">
            <button class="btn btn-danger btn-sm">Elimina selezionati</button>
        </form>

        <table class="table table-bordered table-striped text-center mt-3">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Azioni</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>

                    <!-- RIGA PRINCIPALE -->
                    <tr data-main="<?php echo $row['id']; ?>">

                        <td><input type="checkbox" form="multiForm" name="selected[]" value="<?php echo $row['id']; ?>"></td>

                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>

                        <td>

                            <!-- Modifica -->
                            <button class="btn btn-warning btn-sm" data-edit="<?php echo $row['id']; ?>">Modifica</button>

                            <!-- Elimina singolo -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="azione" value="elimina">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button class="btn btn-danger btn-sm">Elimina</button>
                            </form>

                        </td>
                    </tr>

                    <!-- RIGA MODIFICA NASCOSTA -->
                    <tr data-row="<?php echo $row['id']; ?>" class="edit-row" style="display:none;">
                        <td colspan="5">

                            <form method="POST" class="row g-2">
                                <input type="hidden" name="azione" value="modifica">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                <div class="col-md-4">
                                    <input type="text" name="nome" value="<?php echo htmlspecialchars($row['nome']); ?>" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" class="form-control" required>
                                </div>

                                <div class="col-md-2">
                                    <button class="btn btn-success w-100">Salva</button>
                                </div>

                                <div class="col-md-2">
                                    <button type="button" class="btn btn-secondary w-100" data-close="<?php echo $row['id']; ?>">Chiudi</button>
                                </div>
                            </form>

                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>
                <tr><td colspan="5">Nessun utente registrato.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- ============================================================
                        JAVASCRIPT
============================================================ -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    // ====== Modifica toggle ======
    document.querySelectorAll("[data-edit]").forEach(button => {
        button.addEventListener("click", () => {
            const id  = button.dataset.edit;
            const row = document.querySelector(`[data-row="${id}"]`);

            row.style.display = (row.style.display === "none" || row.style.display === "")
                ? "table-row"
                : "none";
        });
    });

    // ====== Chiudi editor ======
    document.querySelectorAll("[data-close]").forEach(button => {
        button.addEventListener("click", () => {
            const id  = button.dataset.close;
            const row = document.querySelector(`[data-row="${id}"]`);
            row.style.display = "none";
        });
    });

    // ====== Select All ======
    const selectAll = document.getElementById("selectAll");
    selectAll.addEventListener("change", () => {
        document.querySelectorAll('input[name="selected[]"]').forEach(box => {
            box.checked = selectAll.checked;
        });
    });

});
</script>

</body>
</html>

<?php
$conn->close();
?>
