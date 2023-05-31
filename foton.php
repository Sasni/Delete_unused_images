<?php

include('./config/config.inc.php');
include('./init.php');

$shop_root = $_SERVER['DOCUMENT_ROOT'] . '/';

// Manual change of directories from 0 to 9!
// For example, 3 is given 
$image_folder = '/img/p/3';
$scan_dir = $shop_root . $image_folder;


$limit = 1000;
$mode = 0; // 0 = view ONLY, 1 = DELETE

$displayed_images = 0;
$total_size = 0;

function displayImages($directory)
{
    global $shop_root, $image_folder, $displayed_images, $limit, $total_size, $mode;

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

                if ($fileName !== 'fileType' && $extension !== 'php') {
                    $imageId = getImageIdFromPath($imagePath);

                    if (!isImageAssignedToProduct($imageId)) {

	                       	if($mode === 0){
	                       		echo '<tr>';
		                        echo '<td style="border: 1px solid #ccc; padding: 8px;"><img src="' . $imagePath . '" style="max-width: 200px; max-height: 200px;" /></td>';
		                        $fileSize = getFormattedFileSize($file);
		                        $total_size += $fileSize;
		                        echo '<td style="border: 1px solid #ccc; padding: 8px;">' . $fileSize . ' KB</td>';
		                        echo '<td style="border: 1px solid #ccc; padding: 8px;">' . $imagePath . '</td>';
		                        echo '</tr>';

	                       	}elseif($mode === 1 ){
	                       		echo '<tr>';
	                       		$fileSize = getFormattedFileSize($file);
		                        $total_size += $fileSize;

	                       		if (unlink($shop_root . $imagePath)) {
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
    // Extract the image ID from the path
    $fileName = basename($imagePath);
    $imageId = explode('-', $fileName)[0];
    return $imageId;
}

function isImageAssignedToProduct($imageId)
{
    // Check if the image is assigned to any product
    $result = Db::getInstance()->getValue('
        SELECT id_image FROM ' . _DB_PREFIX_ . 'image WHERE id_image = ' . (int)$imageId
    );

    return !empty($result);
}

function getFormattedFileSize($file)
{
    $sizeInBytes = filesize($file);
    $sizeInKB = round($sizeInBytes / 1024, 2);
    return $sizeInKB;
}

echo '<h2>Lista zdjęć:</h2>';
echo '<table style="border: 1px solid #ccc; border-collapse: collapse;">';
echo '<tr><th style="border: 1px solid #ccc; padding: 8px;">Zdjęcie</th><th style="border: 1px solid #ccc; padding: 8px;">Rozmiar (KB)</th></tr>';
displayImages($scan_dir);
echo '</table>';

echo '<p><strong>Całkowity rozmiar zdjęć: ' . $total_size . ' KB</strong></p>';

?>