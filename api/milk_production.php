<?php
header('Content-Type: application/json');
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Allow only authenticated requests
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        // Get milk production records
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;
        
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT mp.*, a.tag_number, a.breed_id, b.name as breed_name FROM milk_production mp JOIN animals a ON mp.animal_id = a.id LEFT JOIN breeds b ON a.breed_id = b.id ORDER BY mp.recording_date DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $milk_data = [];
        while ($row = $result->fetch_assoc()) {
            $milk_data[] = $row;
        }
        
        // Get total count
        $count_result = $conn->query("SELECT COUNT(*) as total FROM milk_production");
        $total = $count_result->fetch_assoc()['total'];
        
        $stmt->close();
        $conn->close();
        
        echo json_encode([
            'milk_production' => $milk_data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        break;
        
    case 'POST':
        // Create new milk production record
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['animal_id']) || !isset($data['session']) || !isset($data['amount_liters'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }
        
        $conn = get_db_connection();
        $stmt = $conn->prepare("INSERT INTO milk_production (animal_id, session, amount_liters, fat_percentage, protein_percentage, somatic_cell_count, recording_date, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdddisi", 
            $data['animal_id'], 
            $data['session'], 
            $data['amount_liters'], 
            $data['fat_percentage'] ?? null, 
            $data['protein_percentage'] ?? null, 
            $data['somatic_cell_count'] ?? null, 
            $data['recording_date'] ?? date('Y-m-d'), 
            $_SESSION['user_id']
        );
        
        if ($stmt->execute()) {
            $record_id = $stmt->insert_id;
            echo json_encode(['success' => true, 'id' => $record_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create record: ' . $stmt->error]);
        }
        
        $stmt->close();
        $conn->close();
        break;
        
    case 'PUT':
        // Update milk production record
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Record ID required']);
            exit;
        }
        
        $id = intval($_GET['id']);
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }
        
        $conn = get_db_connection();
        $stmt = $conn->prepare("UPDATE milk_production SET animal_id = ?, session = ?, amount_liters = ?, fat_percentage = ?, protein_percentage = ?, somatic_cell_count = ?, recording_date = ? WHERE id = ?");
        $stmt->bind_param("isdddsi", 
            $data['animal_id'], 
            $data['session'], 
            $data['amount_liters'], 
            $data['fat_percentage'] ?? null, 
            $data['protein_percentage'] ?? null, 
            $data['somatic_cell_count'] ?? null, 
            $data['recording_date'] ?? date('Y-m-d'),
            $id
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update record: ' . $stmt->error]);
        }
        
        $stmt->close();
        $conn->close();
        break;
        
    case 'DELETE':
        // Delete milk production record
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Record ID required']);
            exit;
        }
        
        $id = intval($_GET['id']);
        $conn = get_db_connection();
        $stmt = $conn->prepare("DELETE FROM milk_production WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete record: ' . $stmt->error]);
        }
        
        $stmt->close();
        $conn->close();
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>