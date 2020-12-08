<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');


function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object !== "." && $object !== "..") {
                if (filetype($dir . "/" . $object) === "dir") {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

function createFileFromChunks($temp_dir, $fileName, $totalSize, $total_files)
{

    $total_files_on_server_size = 0;
    foreach (scandir($temp_dir) as $file) {
        $temp_total = $total_files_on_server_size;
        $tempfilesize = filesize($temp_dir . '/' . $file);
        $total_files_on_server_size = $temp_total + $tempfilesize;
    }
    if ($total_files_on_server_size >= $totalSize) {
        if (($fp = fopen($temp_dir . '/' . $fileName, 'wb')) !== false) {
            for ($i = 1; $i <= $total_files; $i++) {
                fwrite($fp, file_get_contents($temp_dir . '/' . $fileName . '.part' . $i));
            }
            fclose($fp);
        } else {
            return false;
        }

        if (rename($temp_dir, $temp_dir . '_UNUSED')) {
            rrmdir($temp_dir . '_UNUSED');
        } else {
            rrmdir($temp_dir);
        }
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (!(isset($_GET['resumableIdentifier']) && trim($_GET['resumableIdentifier']) !== '')) {
        $_GET['resumableIdentifier'] = '';
    }
    $temp_dir = 'temp/' . $_GET['resumableIdentifier'];
    if (!(isset($_GET['resumableFilename']) && trim($_GET['resumableFilename']) !== '')) {
        $_GET['resumableFilename'] = '';
    }
    if (!(isset($_GET['resumableChunkNumber']) && trim($_GET['resumableChunkNumber']) !== '')) {
        $_GET['resumableChunkNumber'] = '';
    }
    $chunk_file = $temp_dir . '/' . $_GET['resumableFilename'] . '.part' . $_GET['resumableChunkNumber'];
    if (file_exists($chunk_file)) {
        header("HTTP/1.0 200 Ok");
    } else {
        header("HTTP/1.0 404 Not Found");
    }
}

if (!empty($_FILES)) {
    foreach ($_FILES as $file) {

        if ($file['error'] !== 0) {
            continue;
        }

        if (isset($_POST['resumableIdentifier']) && trim($_POST['resumableIdentifier']) !== '') {
            $temp_dir = 'temp/' . $_POST['resumableIdentifier'];
        }
        $dest_file = $temp_dir . '/' . $_POST['resumableFilename'] . '.part' . $_POST['resumableChunkNumber'];

        if (!is_dir($temp_dir) && !mkdir($temp_dir, 0777, true) && !is_dir($temp_dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $temp_dir));
        }

        createFileFromChunks(
            $temp_dir, $_POST['resumableFilename'],
            $_POST['resumableTotalSize'],
            $_POST['resumableTotalChunks']
        );

    }
}

