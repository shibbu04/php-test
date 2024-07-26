<?php

function validateRecipeInput($input) {
    $errors = [];
    if (empty($input['name'])) {
        $errors[] = 'Name is required';
    }
    if (!isset($input['prep_time']) || !is_numeric($input['prep_time']) || $input['prep_time'] < 0) {
        $errors[] = 'Prep time must be a non-negative number';
    }
    if (!isset($input['difficulty']) || !in_array($input['difficulty'], [1, 2, 3])) {
        $errors[] = 'Difficulty must be 1, 2, or 3';
    }
    if (!isset($input['vegetarian']) || !is_bool($input['vegetarian'])) {
        $errors[] = 'Vegetarian must be true or false';
    }
    return $errors;
}

function validateRatingInput($input) {
    $errors = [];
    if (!isset($input['rating']) || !is_numeric($input['rating']) || $input['rating'] < 1 || $input['rating'] > 5) {
        $errors[] = 'Rating must be a number between 1 and 5';
    }
    return $errors;
}

?>