<?php
// Connessione al database MySQL
$servername = 'db';
$username   = 'myuser';
$password   = 'mypassword';
$database   = 'myapp_db';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nome"]) && isset($_POST["email"])) {
    $nome = $_POST["nome"];
    $email = $_POST["email"];

    if (!empty($nome) && !empty($email)) {
        $sql = "INSERT INTO utenti (nome, email) VALUES ('$nome', '$email')";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Utente aggiunto con successo!</p>";
        } else {
            echo "<p>Errore durante l'inserimento: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Compila tutti i campi!</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['azione']) && $_POST['azione'] == 'elimina') {
    $id = $_POST["id"];
    if ($id > 0) {
        $sql = "DELETE FROM utenti WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Utente eliminato!</p>";
        } else {
            echo "<p>Errore durante l'eliminazione: " . $conn->error . "</p>";
        }
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
                                <td>{$row['nome']}</td>
                                <td>{$row['email']}</td>
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
                    echo "<tr><td colspan='4'>Nessun utente trovato</td></tr>";
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