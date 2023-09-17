<?php


if (!function_exists('deleteDirectory')) {
  function deleteDirectory($dir)
  {
    if (!file_exists($dir)) {
      return true;
    }

    if (!is_dir($dir)) {
      return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') {
        continue;
      }

      if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
        return false;
      }
    }

    return rmdir($dir);
  }
}

if (!function_exists('command_exist')) {
  function command_exist($cmd)
  {
    $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
    return !empty($return);
  }
}

if (!function_exists('recursiveCopy')) {
  function recursiveCopy($src, $dst, $childFolder = '')
  {

    // open the source directory
    $dir = opendir($src);

    // Make the destination directory if not exist
    @mkdir($dst);

    // Loop through the files in source directory
    foreach (scandir($src) as $file) {

      if (($file != '.') && ($file != '..')) {
        if (is_dir($src . '/' . $file)) {

          // Recursively calling custom copy function
          // for sub directory
          recursiveCopy($src . '/' . $file, $dst . '/' . $file);
        } else {
          copy($src . '/' . $file, $dst . '/' . $file);
          chmod($dst . '/' . $file, 0777);
        }
      }
    }

    closedir($dir);
  }
}
