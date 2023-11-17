<?php

// Inclure la bibliothèque phpseclib
require 'vendor/autoload.php';

// Chemin vers les fichiers à compresser
$cheminFichiers = '\Users\fabie\OneDrive\Bureau\ecole\cours\fichier';

// Nom de l'archive à créer
$nomArchive = 'archive.zip';

// Chemin vers le répertoire temporaire
$cheminTemporaire = '\Users\fabie\OneDrive\Bureau\ecole\cours\temp';

// Chemin complet de l'archive
$cheminArchive = $cheminTemporaire . '/' . $nomArchive;

// Créer l'archive
if (zipperFichiers($cheminFichiers, $cheminArchive)) {
    echo "Archive créée avec succès.\n";

    // Informations de connexion SFTP
    $serveurSFTP = 'localhost';
    $portSFTP = 22;
    $utilisateurSFTP = 'fabien';
    $motDePasseSFTP = '';

    // Se connecter au serveur SFTP
    $sftp = new phpseclib3\Net\SFTP($serveurSFTP, $portSFTP);
    if (!$sftp->login($utilisateurSFTP, $motDePasseSFTP)) {
        die('Échec de la connexion SFTP');
    }

    // Transférer l'archive sur le serveur distant
    if ($sftp->put($nomArchive, $cheminArchive, NET_SFTP_LOCAL_FILE)) {
        echo "Transfert réussi.\n";
    } else {
        echo "Échec du transfert.\n";
    }

    // Supprimer l'archive temporaire localement
    unlink($cheminArchive);
} else {
    echo "Échec de la création de l'archive.\n";
}

// Fonction pour créer une archive à partir d'un répertoire
function zipperFichiers($cheminSource, $cheminDestination)
{
    $zip = new ZipArchive();
    if ($zip->open($cheminDestination, ZipArchive::CREATE) === TRUE) {
        // Récupérer la liste des fichiers à compresser
        $fichiers = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cheminSource), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($fichiers as $fichier) {
            $fichier = realpath($fichier);

            if (is_dir($fichier)) {
                $zip->addEmptyDir(str_replace($cheminSource . '/', '', $fichier . '/'));
            } elseif (is_file($fichier)) {
                $zip->addFromString(str_replace($cheminSource . '/', '', $fichier), file_get_contents($fichier));
            }
        }

        $zip->close();
        return true;
    } else {
        return false;
    }
}

?>
