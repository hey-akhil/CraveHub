<?php
/**
 * CraveHub - Admin Food Actions API (CRUD)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Admin authorization required']);
    exit;
}

$db = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'save':
        $foodId = trim($_POST['food_id'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Pizzas');
        $price = floatval($_POST['price'] ?? 0);
        $originalPrice = floatval($_POST['original_price'] ?? 0);
        $image = trim($_POST['image'] ?? '');
        $prepTime = trim($_POST['prep_time'] ?? '20 mins');
        $spicyLevel = trim($_POST['spicy_level'] ?? 'Mild');
        $desc = trim($_POST['description'] ?? '');
        $isVeg = isset($_POST['is_veg']) && ($_POST['is_veg'] === '1' || $_POST['is_veg'] === 'true' || $_POST['is_veg'] === 'on');
        $isBestseller = isset($_POST['is_bestseller']) && ($_POST['is_bestseller'] === '1' || $_POST['is_bestseller'] === 'true' || $_POST['is_bestseller'] === 'on');
        $isAvailable = isset($_POST['is_available']) && ($_POST['is_available'] === '1' || $_POST['is_available'] === 'true' || $_POST['is_available'] === 'on');

        if (empty($title) || $price <= 0) {
            echo json_encode(['success' => false, 'error' => 'Title and a valid Price are required']);
            exit;
        }

        if (empty($image)) {
            $image = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=700&auto=format&fit=crop&q=80';
        }

        $categorySlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $category));

        $data = [
            'title' => $title,
            'category' => $category,
            'category_slug' => $categorySlug,
            'price' => $price,
            'original_price' => $originalPrice > 0 ? $originalPrice : ($price + 50),
            'image' => $image,
            'prep_time' => $prepTime,
            'spicy_level' => $spicyLevel,
            'description' => $desc,
            'is_veg' => $isVeg,
            'is_bestseller' => $isBestseller,
            'is_available' => $isAvailable,
            'rating' => 4.8,
            'rating_count' => rand(80, 450)
        ];

        if (!empty($foodId)) {
            // Update
            $db->update('foods', ['_id' => $foodId], $data);
            echo json_encode(['success' => true, 'message' => "Item '{$title}' updated successfully"]);
        } else {
            // Add new
            $doc = $db->insert('foods', $data);
            echo json_encode(['success' => true, 'message' => "Item '{$title}' added to menu", 'item' => $doc]);
        }
        exit;

    case 'toggle_stock':
        $foodId = trim($_POST['food_id'] ?? '');
        $isAvailable = ($_POST['is_available'] ?? '0') === '1';

        if (empty($foodId)) {
            echo json_encode(['success' => false, 'error' => 'Food ID required']);
            exit;
        }

        $db->update('foods', ['_id' => $foodId], ['is_available' => $isAvailable]);
        echo json_encode(['success' => true, 'message' => 'Stock status updated']);
        exit;

    case 'delete':
        $foodId = trim($_POST['food_id'] ?? '');
        if (empty($foodId)) {
            echo json_encode(['success' => false, 'error' => 'Food ID required']);
            exit;
        }

        $db->delete('foods', ['_id' => $foodId]);
        echo json_encode(['success' => true, 'message' => 'Food item deleted successfully']);
        exit;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
        exit;
}
