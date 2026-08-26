<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CenAccount;
use Illuminate\Support\Facades\Auth;

$roles = [
    'MD'          => CenAccount::where('acc_untarea', 'rdw')->first(),
    'Procurement' => CenAccount::where('acc_untarea', 'prc')->first() ?? CenAccount::where('acc_untarea', 'proc')->first(),
    'Finance'     => CenAccount::where('acc_untarea', 'fin')->first(),
    'HR'          => CenAccount::where('acc_untarea', 'hr')->first(),
    'Division'    => CenAccount::where('acc_untarea', 'prj')->first(),
];

echo "=== VERIFYING SERVER-SIDE RENDERED SIDEBAR BADGES ===\n\n";

foreach ($roles as $roleTitle => $user) {
    if (!$user) {
        echo "Role {$roleTitle}: User not found.\n";
        continue;
    }
    Auth::login($user);
    $html = view('welcome')->render();

    echo "Role: {$roleTitle} ({$user->acc_username} - {$user->acc_untarea})\n";

    // Check for badges in HTML
    preg_match_all('/<span class="badge badge-blinking-red ([^"]*)">([^<]*)<\/span>/', $html, $matches);
    
    if (!empty($matches[0])) {
        foreach ($matches[0] as $idx => $fullTag) {
            $classes = $matches[1][$idx];
            $val = $matches[2][$idx];
            $isHidden = str_contains($classes, 'd-none');
            echo "  - Badge: class='{$classes}' => value='{$val}' (visible: " . ($isHidden ? 'hidden' : 'YES, VISIBLE') . ")\n";
        }
    } else {
        echo "  - No badges found in HTML!\n";
    }
    echo "\n";
}
