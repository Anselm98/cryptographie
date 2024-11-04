<?php
ob_start(); 

error_reporting(E_ALL);
ini_set('display_errors', 1);

function getOriginalFilename($filename) {
    $filename = preg_replace('/\.gpg$/', '', $filename);
    $filename = preg_replace('/(\.(jpg|jpeg|png|gif|bmp|pdf|doc|docx|txt))(\..*)?$/i', '$1', $filename);
    return $filename;
}

function cleanupTempFiles($files) {
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

if (isset($_POST['download']) && isset($_POST['passphrase']) && isset($_POST['file'])) {
    $file = $_POST['file'];
    $passphrase = $_POST['passphrase'];
    $filepath = './uploads/' . $file;
    
    $gpgHome = '/var/www/.gnupg';
    putenv("GNUPGHOME=" . $gpgHome);
    putenv("GPG_TTY=");
    
    if (file_exists($filepath)) {
        $tempFile = tempnam(sys_get_temp_dir(), 'gpg_');
        $passPhraseFile = tempnam(sys_get_temp_dir(), 'gpg_pass_');
        chmod($tempFile, 0600);
        chmod($passPhraseFile, 0600);
        
        try {
            file_put_contents($passPhraseFile, $passphrase);
            
            $command = sprintf(
                'gpg --batch --no-tty --yes --pinentry-mode loopback --trust-model always ' .
                '--passphrase-file %s --output %s --decrypt %s 2>&1',
                escapeshellarg($passPhraseFile),
                escapeshellarg($tempFile),
                escapeshellarg($filepath)
            );
            
            exec($command, $output, $returnCode);
            
            error_log("GPG command output: " . implode("\n", $output));
            error_log("Return code: " . $returnCode);
            
            unlink($passPhraseFile);
            
            $outputStr = implode("\n", $output);
            if ($returnCode !== 0 || 
                strpos($outputStr, "failed") !== false ||
                !file_exists($tempFile) || 
                filesize($tempFile) === 0) {
                
                cleanupTempFiles([$tempFile]);
                header("Location: index.php?error=3");
                exit;
            }
            
            $content = file_get_contents($tempFile);
            if ($content === false) {
                cleanupTempFiles([$tempFile]);
                header("Location: index.php?error=2");
                exit;
            }
            
            ob_clean();
            
            $originalFilename = getOriginalFilename(basename($filepath));
            
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $originalFilename . '"');
            header('Content-Length: ' . strlen($content));
            header('Pragma: public');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            
            echo $content;
            
            cleanupTempFiles([$tempFile]);
            exit;
            
        } catch (Exception $e) {
            error_log("Exception in download script: " . $e->getMessage());
            cleanupTempFiles([$tempFile, $passPhraseFile]);
            header("Location: index.php?error=2");
            exit;
        }
    } else {
        header("Location: index.php?error=1");
        exit;
    }
} else {
    header("Location: index.php?error=1");
    exit;
}
?>
