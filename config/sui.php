<?php

return [
    'rpc_url'           => env('SUI_RPC_URL', 'https://public-rpc.sui-testnet.mystenlabs.com'),
    'package_id'        => env('SUI_PACKAGE_ID', '0x80b954a6b2eef980de92c1f1555b8f1ed0b32d9f557714b58836257436979a17'),
    'cli_path'          => env('SUI_CLI_PATH', 'sui'),
    'cli_env'           => env('SUI_CLI_ENV', 'testnet-direct'),
    'node_path'         => env('SUI_NODE_PATH', 'node'),
    'sync_driver'       => env('SUI_SYNC_DRIVER', 'sdk'),
    'gas_budget'        => env('SUI_GAS_BUDGET', 10000000),
    'server_address'    => env('SUI_SERVER_ADDRESS'),
    'server_secret_key' => env('SUI_SERVER_SECRET_KEY'),
    'vault_object_id'   => env('SUI_VAULT_OBJECT_ID'),
];
