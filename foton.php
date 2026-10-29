<?php

include('./config/config.inc.php');
include('./init.php');

$shop_root = $_SERVER['DOCUMENT_ROOT'] . '/';

$limit = 1000;

/*
    MODE
    0 = view ONLY,
    1 = DELETE
*/
$mode = 0; 

$displayed_images = 0;
$total_bytes = 0;
$allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

function displayImages($directory)
{
    global $shop_root, $image_folder, $displayed_images, $limit, $total_size, $mode, $allowed_image_extensions;

    if ($displayed_images >= $limit) {
        return;
    }

    $files = glob($directory . '/*');
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) {
                $imagePath = str_replace($shop_root, '', $file);
                $imagePath = str_replace('//', '/', $imagePath);

                $fileName = basename($imagePath);
                $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

                if (in_array(strtolower($extension), $allowed_image_extensions, true)) {
                    $imageId = getImageIdFromPath($imagePath);
                    if ($imageId <= 0) {
                        continue;
                    }

                    if (!isImageAssignedToProduct($imageId)) {
                        $fileSizeBytes = filesize($file);

                            if($mode === 0){
                                $total_bytes += $fileSizeBytes;
                                $fileSizeKB = round($fileSizeBytes / 1024, 2);
                                echo '<tr>';
                                echo '<td style="border: 1px solid #ccc; padding: 8px;"><img src="' . $imagePath . '" style="max-width: 200px; max-height: 200px;" /></td>';
                            echo '<td style="border: 1px solid #ccc; padding: 8px;">' . $fileSizeKB . ' KB</td>';
                                echo '<td style="border: 1px solid #ccc; padding: 8px;">' . $imagePath . '</td>';
                                echo '</tr>';

                            }elseif($mode === 1 ){
                                echo '<tr>';

                                if (unlink($shop_root . $imagePath)) {
                                    $total_bytes += $fileSizeBytes;
                                    echo '<td style="border: 1px solid #ccc; padding: 8px;"> <p style="color: green;">Zdjęcie zostało usunięte: ' . $imagePath . '</p></td>';
                                } else {
                                    echo '<td style="border: 1px solid #ccc; padding: 8px;"> <p style="color: red;">Błąd usuwania zdjęcia: ' . $imagePath . '</p></td>';
                                }
                                echo '</tr>';
                            }


                        $displayed_images++;
                    }
                }

                if ($displayed_images >= $limit) {
                    break;
                }
            } elseif (is_dir($file)) {
                displayImages($file);
            }
        }
    }
}

function getImageIdFromPath($imagePath)
{
    $fileName = basename($imagePath);
    $imageId = explode('-', $fileName)[0];
    return (int) pathinfo($imageId, PATHINFO_FILENAME);
}

function isImageAssignedToProduct($imageId)
{
    // Check if the image is assigned to any product
    $result = Db::getInstance()->getValue('
        SELECT id_image FROM ' . _DB_PREFIX_ . 'image WHERE id_image = ' . (int)$imageId . '
        UNION
        SELECT id_image FROM ' . _DB_PREFIX_ . 'image_shop WHERE id_image = ' . (int)$imageId . '
        LIMIT 1'
    );

    return !empty($result);
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