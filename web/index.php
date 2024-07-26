<?php
require '../db.php';
require 'validation.php';
require 'auth.php';

ini_set('log_errors', 1);
ini_set('error_log', './error.log');

function logError($message)
{
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, './error.log');
}

// Define API endpoints
$requestMethod = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Define route handlers
$routes = [
    'GET' => [
        '/recipes' => 'listRecipes',
        '/recipes/(\d+)' => 'getRecipe',
        '/recipes/search' => 'searchRecipes',
    ],
    'POST' => [
        '/recipes' => 'createRecipe',
        '/recipes/(\d+)/rating' => 'rateRecipe',
        '/login' => 'loginUser',
    ],
    'PUT' => [
        '/recipes/(\d+)' => 'updateRecipe',
    ],
    'DELETE' => [
        '/recipes/(\d+)' => 'deleteRecipe',
    ],
];

// Route the request
$handled = false;
if (isset($routes[$requestMethod])) {
    foreach ($routes[$requestMethod] as $pattern => $handler) {
        if (preg_match("#^$pattern$#", $path, $matches)) {
            array_shift($matches);
            $handled = true;
            $handler(...$matches);
            break;
        }
    }
}

if (!$handled) {
    sendResponse(['error' => 'Not Found'], 404);
}

function sendResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getRequestBody()
{
    return json_decode(file_get_contents('php://input'), true);
}

function loginUser()
{
    $input = getRequestBody();
    if (!isset($input['username']) || !isset($input['password'])) {
        sendResponse(['error' => 'Username and password are required'], 400);
    }

    $userId = authenticateUser($input['username'], $input['password']);
    if ($userId) {
        $token = generateToken($userId);
        sendResponse(['token' => $token]);
    } else {
        sendResponse(['error' => 'Invalid credentials'], 401);
    }
}

function listRecipes()
{
    global $pdo;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $perPage;

    try {
        $stmt = $pdo->prepare('SELECT * FROM recipes LIMIT ? OFFSET ?');
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $recipes = $stmt->fetchAll();

        $totalStmt = $pdo->query('SELECT COUNT(*) FROM recipes');
        $total = $totalStmt->fetchColumn();

        sendResponse([
            'data' => $recipes,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage
        ]);
    } catch (PDOException $e) {
        logError('Error listing recipes: ' . $e->getMessage());
        sendResponse(['error' => 'An error occurred while listing the recipes'], 500);
    }
}

function getRecipe($id)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT * FROM recipes WHERE id = ?');
        $stmt->execute([$id]);
        $recipe = $stmt->fetch();
        if ($recipe) {
            sendResponse($recipe);
        } else {
            sendResponse(['error' => 'Recipe not found'], 404);
        }
    } catch (PDOException $e) {
        logError('Error getting recipe: ' . $e->getMessage());
        sendResponse(['error' => 'An error occurred while getting the recipe'], 500);
    }
}

function createRecipe()
{
    if (!authenticateRequest()) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    global $pdo;
    $input = getRequestBody();
    $errors = validateRecipeInput($input);
    if (!empty($errors)) {
        sendResponse(['errors' => $errors], 400);
    }

    try {
        if (isset($input['vegetarian'])) {
            $vegetarian = filter_var($input['vegetarian'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        } else {
            sendResponse(['error' => 'Vegetarian field is required'], 400);
        }

        $stmt = $pdo->prepare('INSERT INTO recipes (name, prep_time, difficulty, vegetarian) VALUES (?, ?, ?, ?)');
        $stmt->execute([$input['name'], $input['prep_time'], $input['difficulty'], $vegetarian]);
        sendResponse(['id' => $pdo->lastInsertId()], 201);
    } catch (PDOException $e) {
        logError('Error creating recipe: ' . $e->getMessage());
        sendResponse(['error' => 'An error occurred while creating the recipe'], 500);
    }
}


function updateRecipe($id)
{
    if (!authenticateRequest()) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    global $pdo;
    $input = getRequestBody();
    $errors = validateRecipeInput($input);
    if (!empty($errors)) {
        sendResponse(['errors' => $errors], 400);
    }

    // Check if the recipe exists before updating
    try {
        $checkStmt = $pdo->prepare('SELECT 1 FROM recipes WHERE id = ?');
        $checkStmt->execute([$id]);
        if ($checkStmt->fetchColumn() === false) {
            sendResponse(['error' => 'Recipe not found'], 404);
        }

        // Ensure vegetarian is properly set
        if (isset($input['vegetarian'])) {
            $vegetarian = filter_var($input['vegetarian'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        } else {
            // Handle the case where vegetarian is not provided in the input
            sendResponse(['error' => 'Vegetarian field is required'], 400);
        }

        $stmt = $pdo->prepare('UPDATE recipes SET name = ?, prep_time = ?, difficulty = ?, vegetarian = ? WHERE id = ?');
        $stmt->execute([$input['name'], $input['prep_time'], $input['difficulty'], $vegetarian, $id]);

        sendResponse(['message' => 'Recipe updated successfully'], 200);
    } catch (PDOException $e) {
        logError('Error updating recipe: ' . $e->getMessage());
        sendResponse(['error' => 'An error occurred while updating the recipe'], 500);
    }
}


function deleteRecipe($id)
{
    if (!authenticateRequest()) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    global $pdo;
    try {
        $stmt = $pdo->prepare('DELETE FROM recipes WHERE id = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            sendResponse(['error' => 'Recipe not found'], 404);
        } else {
            sendResponse(['message' => 'Recipe deleted successfully'], 200);
        }
    } catch (PDOException $e) {
        logError('Error deleting recipe: ' . $e->getMessage());
        sendResponse(['error' => 'An error occurred while deleting the recipe'], 500);
    }
}


function logDebug($message)
{
    error_log('[DEBUG] ' . $message);
}


function rateRecipe($id)
{
    global $pdo;
    $input = getRequestBody();
    $errors = validateRatingInput($input);
    if (!empty($errors)) {
        sendResponse(['errors' => $errors], 400);
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO ratings (recipe_id, rating) VALUES (?, ?)');
        $stmt->execute([$id, $input['rating']]);
        sendResponse(['message' => 'Rating added successfully'], 201);
    } catch (PDOException $e) {
        logError('Error rating recipe: ' . $e->getMessage());
        sendResponse(['error' => 'An error occurred while rating the recipe'], 500);
    }
}

function searchRecipes()
{
    global $pdo;
    $query = isset($_GET['q']) ? $_GET['q'] : '';

    try {
        $stmt = $pdo->prepare('SELECT * FROM recipes WHERE name LIKE ?');
        $stmt->execute(['%' . $query . '%']);
        $recipes = $stmt->fetchAll();
        sendResponse(['data' => $recipes]);
    } catch (PDOException $e) {
        logError('Error searching recipes: ' . $e->getMessage());
        sendResponse(['error' => 'An error occurred while searching for recipes'], 500);
    }
}
