<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de fichiers sécurisé GPG</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Gestionnaire de fichiers sécurisé (GPG)</h2>
        
        <?php
        $recipients = [
            'user1' => [
                'name' => 'John Doe',
                'email' => 'john.doe1@example.com'
            ],
            'user2' => [
                'name' => 'Jane Doe',
                'email' => 'jane.doe2@example.com'
            ],
            'user3' => [
                'name' => 'Alice Smith',
                'email' => 'alice.smith@example.com'
            ],
            'user4' => [
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@example.com'
            ]
        ];

        if (isset($_GET['success'])) {
            echo '<div class="message success">Le fichier a été téléversé et chiffré avec succès.</div>';
        }
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $message = match($error) {
                '1' => 'Erreur lors du téléversement du fichier.',
                '2' => 'Erreur lors du chiffrement GPG.',
                '3' => 'Passphrase incorrecte. Veuillez réessayer.',
                default => 'Une erreur est survenue.'
            };
            echo '<div class="message error">' . $message . '</div>';
        }
        ?>
        
        <div class="upload-section">
            <h3>Uploader un fichier</h3>
            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Destinataire:</label>
                    <select name="recipient" required>
                        <option value="">Choisir un destinataire</option>
                        <?php foreach ($recipients as $id => $user): ?>
                            <option value="<?php echo htmlspecialchars($user['email']); ?>">
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fichier:</label>
                    <input type="file" name="file" required>
                </div>
                <button type="submit">Téléverser et Chiffrer</button>
            </form>
        </div>

        <div class="file-list">
            <h3>Fichiers disponibles</h3>
            <?php
            $uploadDir = './uploads/';
            if (is_dir($uploadDir)) {
                $files = scandir($uploadDir);
                $hasFiles = false;
                
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..' && strpos($file, '.gpg') !== false) {
                        $hasFiles = true;
                        
                        if (preg_match('/[^\.]+\.[^\.]+\.(.*?)\.gpg$/', $file, $matches)) {
                            $recipientEmail = $matches[1];
                            $recipientName = 'Unknown';
                            foreach ($recipients as $recipient) {
                                if ($recipient['email'] === $recipientEmail) {
                                    $recipientName = $recipient['name'];
                                    break;
                                }
                            }
                            
                            ?>
                            <div class="file-item">
                                <div class="file-info">
                                    <span class="file-name"><?php echo htmlspecialchars($file); ?></span>
                                    <span class="file-recipient">Destinataire: <?php echo htmlspecialchars($recipientName); ?></span>
                                </div>
                                <form action="download.php" method="POST" class="download-form">
                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
                                    <input type="password" name="passphrase" placeholder="Passphrase GPG" required>
                                    <button type="submit" name="download" class="download-link">Télécharger</button>
                                </form>
                            </div>
                            <?php
                        }
                    }
                }
                
                if (!$hasFiles) {
                    ?>
                    <div class="file-item">Aucun fichier disponible</div>
                    <?php
                }
            } else {
                ?>
                <div class="message error">Le dossier uploads n'existe pas.</div>
                <?php
            }
            ?>
        </div>
    </div>
</body>
</html>
