<?php
// Input validation functions using PDO
require_once __DIR__ . "/database.php";

function validate_required($value, $field_name) {
    if (empty($value) || trim($value) === '') {
        return "$field_name is required";
    }
    return null;
}

function validate_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    return null;
}

function validate_number($value, $field_name, $min = null, $max = null) {
    if (!is_numeric($value)) {
        return "$field_name must be a number";
    }
    if ($min !== null && $value < $min) {
        return "$field_name must be at least $min";
    }
    if ($max !== null && $value > $max) {
        return "$field_name must not exceed $max";
    }
    return null;
}

function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    if (!($d && $d->format($format) === $date)) {
        return "Invalid date format";
    }
    return null;
}

function validate_length($value, $field_name, $min = 0, $max = 255) {
    $length = strlen($value);
    if ($length < $min) {
        return "$field_name must be at least $min characters";
    }
    if ($length > $max) {
        return "$field_name must not exceed $max characters";
    }
    return null;
}

function validate_enum($value, $allowed_values, $field_name) {
    if (!in_array($value, $allowed_values)) {
        return "Invalid $field_name value";
    }
    return null;
}

function validate_tag_number($tag_number) {
    if (empty($tag_number)) {
        return "Tag number is required";
    }

    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare("SELECT id FROM animals WHERE tag_number = ?");
    $stmt->execute([$tag_number]);
    
    if ($stmt->fetch()) {
        return "Tag number already exists";
    }

    return null;
}

function validate_username($username) {
    if (empty($username)) {
        return "Username is required";
    }

    if (strlen($username) < 3) {
        return "Username must be at least 3 characters";
    }

    if (strlen($username) > 50) {
        return "Username must not exceed 50 characters";
    }

    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        return "Username already exists";
    }

    return null;
}

function validate_password($password) {
    if (empty($password)) {
        return "Password is required";
    }

    if (strlen($password) < 6) {
        return "Password must be at least 6 characters";
    }

    return null;
}

function validate_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) < 10 || strlen($phone) > 15) {
        return "Invalid phone number";
    }
    return null;
}

function validate_positive_number($value, $field_name) {
    if (!is_numeric($value) || $value <= 0) {
        return "$field_name must be a positive number";
    }
    return null;
}

function validate_percentage($value, $field_name) {
    if (!is_numeric($value) || $value < 0 || $value > 100) {
        return "$field_name must be between 0 and 100";
    }
    return null;
}

function validate_animal_exists($animal_id) {
    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare("SELECT id FROM animals WHERE id = ?");
    $stmt->execute([$animal_id]);
    
    if (!$stmt->fetch()) {
        return "Animal does not exist";
    }

    return null;
}

function validate_user_exists($user_id) {
    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    if (!$stmt->fetch()) {
        return "User does not exist";
    }

    return null;
}

function get_validation_errors($data, $rules) {
    $errors = [];

    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? null;

        foreach ($rule as $validator => $params) {
            $error = null;

            switch ($validator) {
                case 'required':
                    $error = validate_required($value, $field);
                    break;
                case 'email':
                    $error = validate_email($value);
                    break;
                case 'number':
                    $error = validate_number($value, $field, $params['min'] ?? null, $params['max'] ?? null);
                    break;
                case 'positive':
                    $error = validate_positive_number($value, $field);
                    break;
                case 'percentage':
                    $error = validate_percentage($value, $field);
                    break;
                case 'date':
                    $error = validate_date($value, $params['format'] ?? 'Y-m-d');
                    break;
                case 'length':
                    $error = validate_length($value, $field, $params['min'] ?? 0, $params['max'] ?? 255);
                    break;
                case 'enum':
                    $error = validate_enum($value, $params['values'], $field);
                    break;
                case 'tag_number':
                    $error = validate_tag_number($value);
                    break;
                case 'username':
                    $error = validate_username($value);
                    break;
                case 'password':
                    $error = validate_password($value);
                    break;
                case 'phone':
                    $error = validate_phone($value);
                    break;
            }

            if ($error) {
                $errors[$field] = $error;
            }
        }
    }

    return $errors;
}

function sanitize_array($array) {
    return array_map(function($value) {
        return is_string($value) ? sanitize_input($value) : $value;
    }, $array);
}
?>
