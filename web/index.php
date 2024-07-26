<?php
// Include database connection
require '../db.php';

// Define API endpoints
$requestMethod = $_SERVER["REQUEST_METHOD"];
$path = $_SERVER['REQUEST_URI'];

switch ($requestMethod) {
    case 'GET':
        if (preg_match('/^\/recipes\/(\d+)$/', $path, $matches)) {
            getRecipe($matches[1]);
        } else {
            listRecipes();
        }
        break;
    case 'POST':
        if (preg_match('/^\/recipes\/(\d+)\/rating$/', $path, $matches)) {
            rateRecipe($matches[1]);
        } else {
            createRecipe();
        }
        break;
    case 'PUT':
    case 'PATCH':
        if (preg_match('/^\/recipes\/(\d+)$/', $path, $matches)) {
            updateRecipe($matches[1]);
        }
        break;
    case 'DELETE':
        if (preg_match('/^\/recipes\/(\d+)$/', $path, $matches)) {
            deleteRecipe($matches[1]);
        }
        break;
    default:
        header("HTTP/1.1 405 Method Not Allowed");
        break;
}

function listRecipes() {
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM recipes');
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($recipes);
}

function getRecipe($id) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM recipes WHERE id = ?');
    $stmt->execute([$id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($recipe) {
        header('Content-Type: application/json');
        echo json_encode($recipe);
    } else {
        header("HTTP/1.1 404 Not Found");
    }
}

function createRecipe() {
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare('INSERT INTO recipes (name, prep_time, difficulty, vegetarian) VALUES (?, ?, ?, ?)');
    $stmt->execute([$input['name'], $input['prep_time'], $input['difficulty'], $input['vegetarian']]);
    header('Content-Type: application/json');
    echo json_encode(['id' => $pdo->lastInsertId()]);
}

function updateRecipe($id) {
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare('UPDATE recipes SET name = ?, prep_time = ?, difficulty = ?, vegetarian = ? WHERE id = ?');
    $stmt->execute([$input['name'], $input['prep_time'], $input['difficulty'], $input['vegetarian'], $id]);
    header("HTTP/1.1 204 No Content");
}

function deleteRecipe($id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM recipes WHERE id = ?');
    $stmt->execute([$id]);
    header("HTTP/1.1 204 No Content");
}

function rateRecipe($id) {
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare('INSERT INTO ratings (recipe_id, rating) VALUES (?, ?)');
    $stmt->execute([$id, $input['rating']]);
    header("HTTP/1.1 201 Created");
}
?>
