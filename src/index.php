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

$GROQ_API_KEY = "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";
$GROQ_MODEL   = "qwen/qwen3-32b";

$domanda  = "";
$risposta = "";
$reasoning = "";
$errore   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $domanda = trim($_POST["domanda"] ?? "");

    if ($domanda === "") {
        $errore = "Per favore scrivi una domanda prima di inviare.";
    } else {

        $body = [
            "model"    => $GROQ_MODEL,
            "messages" => [
                [
                    "role"    => "system",
                    "content" => "Sei un assistente utile e conciso che risponde in italiano."
                ],
                [
                    "role"    => "user",
                    "content" => $domanda
                ]
            ]
        ];

        $bodyJson = json_encode($body);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.groq.com/openai/v1/chat/completions");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $GROQ_API_KEY
        ]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseRaw = curl_exec($ch);

        if (curl_errno($ch)) {
            $errore = "Errore di connessione: " . curl_error($ch);
        } else {
            $responseData = json_decode($responseRaw, true);

            if (isset($responseData["error"])) {
                $errore = "Errore API Groq: " . $responseData["error"]["message"];
            } else {
                $risposta = $responseData["choices"][0]["message"]["content"] ?? "Nessuna risposta ricevuta.";
                $reasoning = $responseData["choices"][0]["message"]["reasoning"] ?? "";
            }
        }

        curl_close($ch);
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Chat con Groq</title>
</head>
<body>
 
<h1>Chatta con Groq AI</h1>
<p>Modello in uso: <b><?php echo htmlspecialchars($GROQ_MODEL); ?></b></p>
 
<form method="POST" action="">
    <label for="domanda">La tua domanda:</label>
    <br>
    <input type="text" id="domanda" name="domanda" size="60"
           value="<?php echo htmlspecialchars($domanda); ?>">
    <br><br>
    <input type="submit" value="Invia">
</form>
 
<?php if ($errore !== ""): ?>
    <hr>
    <p><b>Errore:</b> <?php echo htmlspecialchars($errore); ?></p>
 
<?php elseif ($risposta !== ""): ?>
    <hr>
 
    <h2>La tua domanda</h2>
    <p><?php echo nl2br(htmlspecialchars($domanda)); ?></p>
 
    <?php if ($reasoning !== ""): ?>
        <h2>Ragionamento del modello (Reasoning)</h2>
        <p><?php echo nl2br(htmlspecialchars($reasoning)); ?></p>
    <?php endif; ?>
 
    <h2>Risposta del modello</h2>
    <p><?php echo nl2br(htmlspecialchars($risposta)); ?></p>
 
<?php endif; ?>
 
</body>
</html>