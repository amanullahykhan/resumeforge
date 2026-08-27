<?php
// Quick web-based composer installer for shared hosting environments
ini_set('max_execution_time', 300);
echo "<pre>Starting Dependency Installation...\n\n";

$composerUrl = 'https://getcomposer.org/composer-stable.phar';
$composerFile = __DIR__ . '/composer.phar';

echo "1. Downloading Composer...\n";
if (!file_exists($composerFile)) {
    file_put_contents($composerFile, file_get_contents($composerUrl));
}
echo "   Done.\n\n";

echo "2. Running composer update (this may take a minute)...\n";
putenv('COMPOSER_HOME=' . __DIR__ . '/.composer');
$output = [];
$return_var = 0;
exec('php composer.phar update --ignore-platform-reqs 2>&1', $output, $return_var);

echo implode("\n", $output);
echo "\n\n   Finished with exit code: $return_var\n";

echo "\n3. Cleaning up...\n";
@unlink($composerFile);
echo "   Done.\n\n";

if ($return_var === 0) {
    echo "SUCCESS! The dompdf package is now installed. PDF downloads should now work normally. You can delete this file.";
} else {
    echo "WARNING: Composer encountered an error. Check the output above.";
}
echo "</pre>";
