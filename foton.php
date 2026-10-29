<?php

include('./config/config.inc.php');
include('./init.php');

$shop_root = $_SERVER['DOCUMENT_ROOT'] . '/';

$limit = 100;

/*
    MODE
    0 = view ONLY,
    1 = DELETE
*/
$mode = 0; 
$allowed_delete_ip = 'WPISZ_TUTAJ_SWOJE_IP';
$remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';
$escaped_remote_ip = htmlspecialchars($remote_ip, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

echo '<p>Adres IP połączenia: ' . $escaped_remote_ip . '</p>';

if ($mode === 1) {
    if (filter_var($allowed_delete_ip, FILTER_VALIDATE_IP) === false || $remote_ip !== $allowed_delete_ip) {
        http_response_code(403);
        exit('Brak dostępu do trybu kasowania z tego adresu IP.');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        echo '<form method="post">';
        echo '<button type="submit">Potwierdź kasowanie</button>';
        echo '</form>';
        exit;
    }
}

$displayed_images = 0;
$total_bytes = 0;
$allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$image_rows = Db::getInstance()->executeS('
    SELECT id_image FROM ' . _DB_PREFIX_ . 'image
    UNION
    SELECT id_image FROM ' . _DB_PREFIX_ . 'image_shop'
);

if ($image_rows === false) {
    die('Nie udało się pobrać listy obrazów z bazy.');
}

$image_ids = array_flip(array_column($image_rows, 'id_image'));

function displayImages($directory)
{
    global $shop_root, $displayed_images, $limit, $total_bytes, $mode, $allowed_image_extensions, $image_ids;

    if ($displayed_images >= $limit || !is_dir($directory) || !is_readable($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY,
        RecursiveIteratorIterator::CATCH_GET_CHILD
    );

    foreach ($iterator as $entry) {
        if (!$entry->isFile()) {
            continue;
        }

        $file = $entry->getPathname();
        $imagePath = str_replace($shop_root, '', $file);
        $imagePath = str_replace('//', '/', $imagePath);
        $escapedImagePath = htmlspecialchars($imagePath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        if (!in_array(strtolower($extension), $allowed_image_extensions, true)) {
            continue;
        }

        $imageId = getImageIdFromPath($imagePath);
        if ($imageId <= 0 || isset($image_ids[$imageId])) {
            continue;
        }

        $fileSizeBytes = filesize($file);

        if ($mode === 0) {
            $total_bytes += $fileSizeBytes;
            $fileSizeKB = round($fileSizeBytes / 1024, 2);
            echo '<tr>';
            echo '<td style="border: 1px solid #ccc; padding: 8px;"><img src="' . $escapedImagePath . '" style="max-width: 200px; max-height: 200px;" /></td>';
            echo '<td style="border: 1px solid #ccc; padding: 8px;">' . $fileSizeKB . ' KB</td>';
            echo '<td style="border: 1px solid #ccc; padding: 8px;">' . $escapedImagePath . '</td>';
            echo '</tr>';
        } elseif ($mode === 1) {
            echo '<tr>';

            if (unlink($file)) {
                $total_bytes += $fileSizeBytes;
                echo '<td style="border: 1px solid #ccc; padding: 8px;"><p style="color: green;">Zdjęcie zostało usunięte: ' . $escapedImagePath . '</p></td>';
            } else {
                echo '<td style="border: 1px solid #ccc; padding: 8px;"><p style="color: red;">Błąd usuwania zdjęcia: ' . $escapedImagePath . '</p></td>';
            }

            echo '</tr>';
        }

        $displayed_images++;
        if ($displayed_images >= $limit) {
            break;
        }
    }
}

function getImageIdFromPath($imagePath)
{
    $fileName = basename($imagePath);
    $imageId = explode('-', $fileName)[0];
    return (int) pathinfo($imageId, PATHINFO_FILENAME);
}

echo '<h2>Lista zdjęć:</h2>';
echo '<table style="border: 1px solid #ccc; border-collapse: collapse;">';
echo '<tr><th style="border: 1px solid #ccc; padding: 8px;">Zdjęcie</th><th style="border: 1px solid #ccc; padding: 8px;">Rozmiar (KB)</th></tr>';

for ($i = 1; $i <= 9; $i++) {
    $image_folder = '/img/p/' . $i;
    $scan_dir = $shop_root . $image_folder;
    displayImages($scan_dir);
}

echo '</table>';

echo '<p><strong>Całkowity rozmiar zdjęć: ' . round($total_bytes / 1048576, 2) . ' MB</strong></p>';

?>