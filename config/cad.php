<?php

return [

    'owner_ids' => explode('|', env('OWNER_IDS')),

    // Forwarded HTTPS scheme/host can only be trusted from these proxies.
    // Unset by default for direct-host installations.
    'trusted_proxies' => env('TRUSTED_PROXIES'),

];
