<?php
namespace Diapositive\Helpers;

class FileSystem {

    public static function countTotalFiles($directory_path) {
        return iterator_count(new \FilesystemIterator($directory_path, \FilesystemIterator::SKIP_DOTS));
    }

    public static function removeDirectory($directory_path) {
        $directories        = new \RecursiveDirectoryIterator($directory_path, \FilesystemIterator::SKIP_DOTS);
        $directory_iterator = new \RecursiveIteratorIterator($directories, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach($directory_iterator as $item) {
            $item->isDir() ? rmdir($item) : unlink($item);
        }

        rmdir($directory_path);
    }

}
