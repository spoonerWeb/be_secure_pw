<?php

use SpoonerWeb\BeSecurePw\Controller\ForcePasswordChangeController;

return [
    'force_password_change' => [
        'path' => '/user/force_password_change',
        'referrer' => 'required,refresh-always',
        'target' => ForcePasswordChangeController::class . '::forceAction',
    ],
];
