<?php
// This script fetches a recipe and serves it as a downloadable .txt file.

require_once 'config.php';

// Get recipe ID from URL, ensuring it's an integer.
$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($recipe_id === 0) {
    // If no valid ID is provided, do nothing.
    exit('Invalid recipe ID.');
}

try {
    // Fetch the recipe from the database.
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ?");
    $stmt->execute([$recipe_id]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        exit('Recipe not found.');
    }

    // --- Start building the plain text content ---
    $filename = strtolower(str_replace(' ', '-', $recipe['title'])) . '.txt';
    $content = "";

    // Title and separator
    $content .= strtoupper($recipe['title']) . "\n";
    $content .= str_repeat('=', strlen($recipe['title'])) . "\n\n";

    // Description
    $content .= "Description:\n";
    $content .= wordwrap($recipe['description'], 80) . "\n\n";

    // Meta Info
    $content .= "Prep Time: " . $recipe['prep_time'] . " minutes\n";
    $content .= "Cook Time: " . $recipe['cook_time'] . " minutes\n";
    $content .= "Yields: " . $recipe['yields'] . "\n\n";

    // Ingredients
    $content .= "--- INGREDIENTS ---\n";
    $ingredients = json_decode($recipe['ingredients'], true);
    if (is_array($ingredients)) {
        foreach ($ingredients as $item) {
            $content .= "- " . $item . "\n";
        }
    }
    $content .= "\n";

    // Instructions
    $content .= "--- INSTRUCTIONS ---\n";
    $instructions = json_decode($recipe['instructions'], true);
    if (is_array($instructions)) {
        $step = 1;
        foreach ($instructions as $item) {
            $content .= $step . ". " . wordwrap($item, 80, "\n   ") . "\n";
            $step++;
        }
    }
    $content .= "\n";

    // Chef's Notes
    if (!empty($recipe['notes'])) {
        $content .= "--- CHEF'S NOTES ---\n";
        $content .= wordwrap($recipe['notes'], 80) . "\n\n";
    }
    
    // --- Set headers to trigger download ---
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));

    // Output the content.
    echo $content;
    exit();

} catch (PDOException $e) {
    // Handle database errors gracefully.
    header('HTTP/1.1 500 Internal Server Error');
    exit('Database error occurred.');
}
?>
