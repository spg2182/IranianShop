<?php
require_once('includes/init.php');
require_once('includes/db_link.php');

header('Content-Type: application/json');

// جلوگیری از هرگونه خروجی قبل از JSON
ob_start();

$province_id = filter_input(INPUT_GET, 'province_id', FILTER_VALIDATE_INT);

if ($province_id) {
    $stmt = $link->prepare("SELECT id, name FROM cities WHERE province_id = ? ORDER BY name");
    $stmt->bind_param("i", $province_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cities = [];
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row;
    }
    
    ob_end_clean();
    echo json_encode($cities);
} else {
    ob_end_clean();
    echo json_encode([]);
}
?>