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
        // Get all animals or specific animal
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $conn = get_db_connection();
            $stmt = $conn->prepare("SELECT a.*, b.name as breed_name FROM animals a LEFT JOIN breeds b ON a.breed_id = b.id WHERE a.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $animal = $result->fetch_assoc();
            $stmt->close();
            $conn->close();
            
            if ($animal) {
                echo json_encode($animal);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Animal not found']);
            }
        } else {
            // Get all animals with pagination
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $offset = ($page - 1) * $limit;
            
            $conn = get_db_connection();
            $stmt = $conn->prepare("SELECT a.*, b.name as breed_name FROM animals a LEFT JOIN breeds b ON a.breed_id = b.id ORDER BY a.created_at DESC LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            $animals = [];
            while ($row = $result->fetch_assoc()) {
                $animals[] = $row;
            }
            
            // Get total count
            $count_result = $conn->query("SELECT COUNT(*) as total FROM animals");
            $total = $count_result->fetch_assoc()['total'];
            
            $stmt->close();
            $conn->close();
            
            echo json_encode([
                'animals' => $animals,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create new animal
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['tag_number'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }
        
        $conn = get_db_connection();
        $stmt = $conn->prepare("INSERT INTO animals (tag_number, breed_id, birth_date, gender, weight, status, parent_sire_id, parent_dam_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisdsdsss", 
            $data['tag_number'], 
            $data['breed_id'] ?? null, 
            $data['birth_date'] ?? null, 
            $data['gender'], 
            $data['weight'] ?? null, 
            $data['status'] ?? 'Active', 
            $data['parent_sire_id'] ?? null, 
            $data['parent_dam_id'] ?? null, 
            $data['notes'] ?? null
        );
        
        if ($stmt->execute()) {
            $animal_id = $stmt->insert_id;
            echo json_encode(['success' => true, 'id' => $animal_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create animal: ' . $stmt->error]);
        }
        
        $stmt->close();
        $conn->close();
        break;
        
    case 'PUT':
        // Update animal
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Animal ID required']);
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
        $stmt = $conn->prepare("UPDATE animals SET tag_number = ?, breed_id = ?, birth_date = ?, gender = ?, weight = ?, status = ?, parent_sire_id = ?, parent_dam_id = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("sisdsdsssi", 
            $data['tag_number'], 
            $data['breed_id'] ?? null, 
            $data['birth_date'] ?? null, 
            $data['gender'], 
            $data['weight'] ?? null, 
            $data['status'] ?? 'Active', 
            $data['parent_sire_id'] ?? null, 
            $data['parent_dam_id'] ?? null, 
            $data['notes'] ?? null,
            $id
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update animal: ' . $stmt->error]);
        }
        
        $stmt->close();
        $conn->close();
        break;
        
    case 'DELETE':
        // Delete animal
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Animal ID required']);
            exit;
        }
        
        $id = intval($_GET['id']);
        $conn = get_db_connection();
        $stmt = $conn->prepare("DELETE FROM animals WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete animal: ' . $stmt->error]);
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