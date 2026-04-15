<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animal_id = intval($_POST['animal_id']);
    $insemination_date = $_POST['insemination_date'];
    $type = $_POST['type'];
    $sire_details = sanitize_input($_POST['sire_details'] ?? '');
    
    $conn = get_db_connection();
    $stmt = $conn->prepare("INSERT INTO inseminations (animal_id, insemination_date, type, sire_details, performed_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $animal_id, $insemination_date, $type, $sire_details, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $insemination_id = $conn->insert_id;
        
        $expected_calving = date('Y-m-d', strtotime($insemination_date . ' + 280 days'));
        $stmt2 = $conn->prepare("INSERT INTO pregnancies (animal_id, insemination_id, confirmation_date, expected_calving_date, status) VALUES (?, ?, ?, ?, 'Confirmed')");
        $stmt2->bind_param("isss", $animal_id, $insemination_id, $insemination_date, $expected_calving);
        $stmt2->execute();
        $stmt2->close();
        
        log_activity("Recorded insemination for animal ID: $animal_id");
        $_SESSION['success'] = "Insemination recorded successfully!";
    } else {
        $_SESSION['error'] = "Failed to record insemination.";
    }
    
    $stmt->close();
    $conn->close();
}

redirect('reproduction');
?>