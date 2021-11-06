<?php

namespace App\Core\Uploader;

class Uploader
{
    const MAX_SIZE = 5;
    const ALLOWED_IMAGE_FORMATS = ['image/png', 'image/jpg', 'image/jpeg'];
    const ALLOWED_DOCUMENT_FORMATS = ['application/pdf'];
    const CHARACTERS = [
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y',
        'Ŕ'=>'R', 'ŕ'=>'r', ' '=>'-'
    ];
    const ERROR_MESSAGE = 'Une erreur est survenue pendant le téléchargement du fichier, veuillez réessayer';


    public function isValidImage($file): bool
    {
        return $this->isValidFile($file, self::ALLOWED_IMAGE_FORMATS);
    }


    public function isValidDocument($file): bool
    {
        return $this->isValidFile($file, self::ALLOWED_DOCUMENT_FORMATS);
    }


    private function isValidFile(array $file, array $formats): bool
    {
        if (!in_array($file['type'], $formats) && $file['error'] == 0) {
            return false;
        }

        return $file['size'] / 1024 / 1024 < self::MAX_SIZE;
    }


    public function saveFile(array $file, $name, $path): string
    {
        $extension = explode('/', $file['type'])[1];
        $name = strtolower(strtr($name, self::CHARACTERS));

        chmod($path, 0777);
        if (move_uploaded_file($file['tmp_name'], $path.$name.'.'.$extension)) {
            return $name.'.'.$extension;
        }
    }
}
