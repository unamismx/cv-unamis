<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$compiler = $app->make('blade.compiler');
$path = resource_path('views/pdf/cv_template.blade.php');
$compiled = $compiler->compileString(file_get_contents($path));
file_put_contents('/tmp/cv_template_compiled.php', $compiled);
echo "ok\n";
