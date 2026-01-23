<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check user
try {
    $user = App\Models\User::where('email', 'test@example.com')->first();
    
    echo "=== User Information ===\n";
    if ($user) {
        echo "Email: " . $user->email . "\n";
        echo "Name: " . $user->name . "\n";
        echo "Role: " . $user->role . "\n";
        echo "ID: " . $user->id . "\n";
        echo "Verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n";
    } else {
        echo "User not found!\n";
    }

    // Check categories
    $categories = App\Models\Category::all();
    echo "\n=== Categories ===\n";
    echo "Count: " . $categories->count() . "\n";
    foreach ($categories as $category) {
        echo "- " . $category->name . " (ID: " . $category->id . ")\n";
    }

    // Check if any quizzes exist
    $quizzes = App\Models\Quiz::all();
    echo "\n=== Quizzes ===\n";
    echo "Count: " . $quizzes->count() . "\n";
    foreach ($quizzes as $quiz) {
        echo "- " . $quiz->title . " (ID: " . $quiz->id . ")\n";
        echo "  Category: " . $quiz->category->name . "\n";
        echo "  User: " . $quiz->user->email . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>