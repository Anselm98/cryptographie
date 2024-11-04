<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uploadDir = './uploads/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (isset($_FILES['file']) && isset($_POST['recipient'])) {
        $file = $_FILES['file'];
        $recipient = $_POST['recipient'];
        $fileName = basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $encryptedPath = $uploadDir . $fileName . '.' . $recipient . '.gpg';
            
            putenv("GNUPGHOME=/var/www/.gnupg");
            
            $command = sprintf(
                'gpg --encrypt --recipient %s --trust-model always --output %s %s 2>&1',
                escapeshellarg($recipient),
                escapeshellarg($encryptedPath),
                escapeshellarg($targetPath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                unlink($targetPath);
                header('Location: index.php?success=1');
                exit;
            } else {
                unlink($targetPath);
                error_log("GPG encryption failed: " . implode("\n", $output));
                header('Location: index.php?error=2');
                exit;
            }
        } else {
            header('Location: index.php?error=1');
            exit;
        }
    }
}
header('Location: index.php');
exit;
?>
