<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $image, ?string $folder = '')
    {
        // Je limite la taille du fichier en octets (5 Mo)
        $maxFileSize = 5 * 1024 * 1024;
        if ($image->getSize() > $maxFileSize) {
            throw new Exception('File too large.');
        }

        // Je vérifie l'extension du fichier
        $allowedExtensions = ['png', 'jpeg', 'jpg', 'webp'];
        $extension = strtolower(pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Incorrect file format.');
        }

        // Je vérifie le type MIME
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($image);
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception('Format not allowed.');
        }

        // Je donne un nouveau nom à l'image
        $file = bin2hex(random_bytes(16)) . '.webp';

        // Je récupère les infos de l'image
        $imageInfos = getimagesize($image);

        if ($imageInfos === false) {
            throw new Exception('Incorrect image format.');
        }

        // Je limite les dimensions de l'image
        $maxWidth = 2000;
        $maxHeight = 2000;
        $imageWidth = $imageInfos[0];
        $imageHeight = $imageInfos[1];
        if ($imageWidth > $maxWidth || $imageHeight > $maxHeight) {
            throw new Exception('Image too large.');
        }

        // Je vérifie le format de l'image
        switch ($imageInfos['mime']) {
            case 'image/png':
                $imageSource = imagecreatefrompng($image);
                break;
            case 'image/jpeg':
                $imageSource = imagecreatefromjpeg($image);
                break;
            case 'image/webp':
                $imageSource = imagecreatefromwebp($image);
                break;
            default:
                throw new Exception('Incorrect image format.');
        }

        // Chemin vers le dossier de destination
        $path = $this->params->get('images_directory') . $folder;

        // Je crée le dossier de destination s'il n'existe pas
        if (!file_exists($path . '/')) {
            mkdir($path . '/', 0755, true);
        }

        // Je converti l'image au format webp
        $webpPath = $path . '/' . $file;
        if (!imagewebp($imageSource, $webpPath)) {
            throw new Exception('Error converting image to webp.');
        }

        // Je libère la mémoire
        imagedestroy($imageSource);

        return $file;
    }

    public function delete(string $file, ?string $folder = '')
    {
        if ($file !== 'default.webp') {
            $success = false;
            $path = $this->params->get('images_directory') . $folder;

            $original = $path . '/' . $file;

            if (file_exists($original)) {
                unlink($original);
                $success = true;
            }
            return $success;
        }
        return false;
    }
}
