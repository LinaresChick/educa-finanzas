<?php
namespace Core;

/**
 * Tool para buscar y reemplazar texto en archivos
 */
class Replace {
    public static function replaceInFile($filePath, $search, $replace) {
        if (!file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);
        $newContent = str_replace($search, $replace, $content);
        
        if ($content === $newContent) {
            return false;
        }

        return file_put_contents($filePath, $newContent) !== false;
    }
}