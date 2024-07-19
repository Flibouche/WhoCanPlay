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

    public function add(UploadedFile $image, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        // On donne un nouveau nom à l'image
        $file = md5(uniqid(rand(), true)) . '.webp';

        // On récupère les infos de l'image
        $imageInfos = getimagesize($image);

        if($imageInfos === false){
            throw new Exception('Incorrect image format.');
        }

        // On vérifie le format de l'image
        switch($imageInfos['mime']){
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

        // On recadre l'image
        // On récupère les dimensions
        $imageWidth = $imageInfos[0];
        $imageHeight = $imageInfos[1];

        // On vérifie l'orientation de l'image
        switch ($imageWidth <=> $imageHeight){
            case -1: // portrait
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = ($imageHeight - $squareSize) / 2;
                break;
            case 0: // carré
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = 0;
                break;
            case 1: // paysage
                $squareSize = $imageHeight;
                $src_x = ($imageWidth - $squareSize) / 2;
                $src_y = 0;
                break;
        }

        // On crée une nouvelle image "vierge"
        $resizedImage = imagecreatetruecolor($width, $height);

        imagecopyresampled($resizedImage, $imageSource, 0, 0, $src_x, $src_y, $width, $height, $squareSize, $squareSize);

        $path = $this->params->get('images_directory') . $folder;

        // On crée le dossier de destination s'il n'existe pas
        if(!file_exists($path . '/mini/')){
            mkdir($path . '/mini/', 0755, true);
        }

        // On stocke l'image recadrée
        imagewebp($resizedImage, $path . '/mini/' . $width . 'x' . $height . '-' . $file);

        $image->move($path . '/', $file);

        return $file;
    }

    public function delete(string $file, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        if($file !== 'default.webp'){
            $success = false;
            $path = $this->params->get('images_directory') . $folder;

            $mini = $path . '/mini/' . $width . 'x' . $height . '-' . $file;

            if(file_exists($mini)){
                unlink($mini);
                $success = true;
            }

            $original = $path . '/' . $file;

            if(file_exists($original)){
                unlink($original);
                $success = true;
            }
            return $success;
        }
        return false;
    }
}