<?php
$servername = 'db';
$username = 'myuser';
$password = 'mypassword';
$database = 'myapp_db';

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$sql = "DROP DATABASE IF EXISTS $database";
$conn->query($sql);

$sql = "CREATE DATABASE $database";
if (!$conn->query($sql)) {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS utenti (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL
)";

if (!$conn->query($sql)) {
    echo "Error creating table utenti: " . $conn->error;
}

$sql = "CREATE TABLE IF NOT EXISTS anagrafica (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    eta INT(6),
    citta VARCHAR(100),
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";


if (!$conn->query($sql)) {
    echo "Error creating table anagrafica: " . $conn->error;
}

$sql = "SELECT * FROM utenti WHERE username='admin'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO utenti (username, password) VALUES ('admin', 'admin')";
    $conn->query($sql);
}

$errore = '';
$messaggio = '';
$logged_in = false;
$user_logged = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    
    $sql = "SELECT * FROM utenti WHERE username='$user' AND password='$pass'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $logged_in = true;
        $user_logged = $user;
    } else {
        $errore = 'Username o password errati!';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['inserisci'])) {
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $eta = $_POST['eta'];
    $citta = $_POST['citta'];
    
    $sql = "INSERT INTO anagrafica (nome, cognome, eta, citta) 
            VALUES ('$nome', '$cognome', '$eta', '$citta')";
    
    if ($conn->query($sql)) {
        $messaggio = 'New record created successfully';
        $logged_in = true;
        $user_logged = $_POST['user_logged'];
    } else {
        $messaggio = 'Error: ' . $conn->error;
    }
}

if (!$logged_in) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    
    <?php if ($errore): ?>
        <p><strong><?php echo $errore; ?></strong></p>
    <?php endif; ?>
    
    <form method="POST">
        <p>
            <label>Username:</label><br>
            <input type="text" name="username" required>
        </p>
        
        <p>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </p>
        
        <p>
            <button type="submit" name="login">Accedi</button>
        </p>
    </form>
    
    <p><em>Credenziali: admin / admin</em></p>
</body>
</html>

<?php
} else {
    $sql = "SELECT * FROM anagrafica";
    $result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestione Anagrafica</title>
</head>
<body>
    <h1>Benvenuto, <?php echo $user_logged; ?>!</h1>
    
    <hr>
    
    <h2>Inserisci Nuova Persona</h2>
    
    <?php if ($messaggio): ?>
        <p><strong><?php echo $messaggio; ?></strong></p>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="user_logged" value="<?php echo $user_logged; ?>">
        
        <p>
            <label>Nome:</label><br>
            <input type="text" name="nome" required>
        </p>
        
        <p>
            <label>Cognome:</label><br>
            <input type="text" name="cognome" required>
        </p>
        
        <p>
            <label>Età:</label><br>
            <input type="number" name="eta">
        </p>
        
        <p>
            <label>Città:</label><br>
            <input type="text" name="citta">
        </p>
        
        <p>
            <button type="submit" name="inserisci">Inserisci</button>
        </p>
    </form>
    
    <hr>
    
    <h2>Elenco Persone</h2>
    
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Età</th>
            <th>Città</th>
            <th>Data Registrazione</th>
        </tr>
        
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['nome']; ?></td>
            <td><?php echo $row['cognome']; ?></td>
            <td><?php echo $row['eta']; ?></td>
            <td><?php echo $row['citta']; ?></td>
            <td><?php echo $row['reg_date']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
}

$conn->close();
?>