<?php

return [

    // Future AdminCP toggle: enable/disable the UK FMS-style assignment prompt.
    'prompt_enroute_on_assignment' => env('CAD_PROMPT_ENROUTE_ON_ASSIGNMENT', true),

    'owner_ids' => explode('|', env('OWNER_IDS')),

    // Forwarded HTTPS scheme/host can only be trusted from these proxies.
    // Unset by default for direct-host installations.
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    // Comma-separated MDT subdivision options.
    'mdt_subdivisions' => array_values(array_filter(array_map('trim',
        explode(',', env('MDT_SUBDIVISIONS', 'Patrol,Traffic,Investigations,Special Operations'))
    ))),
];
