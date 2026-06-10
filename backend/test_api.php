<?php
// Simple test script to check if models and database are accessible

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    // Test Program model
    $programs = \App\Models\Program::where('is_active', true)->latest()->get();
    echo "Programs found: " . count($programs) . "\n";
    
    if (count($programs) > 0) {
        echo "First program: " . $programs[0]->title . "\n";
    }
    
    // Test News model
    $news = \App\Models\News::latest('publication_date')->get();
    echo "News found: " . count($news) . "\n";
    
    if (count($news) > 0) {
        echo "First news: " . $news[0]->title . "\n";
    }
    
    // Test Testimonial model
    $testimonials = \App\Models\Testimonial::latest()->get();
    echo "Testimonials found: " . count($testimonials) . "\n";
    
    if (count($testimonials) > 0) {
        echo "First testimonial: " . $testimonials[0]->name . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
