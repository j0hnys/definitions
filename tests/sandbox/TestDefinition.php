<?php

namespace j0hnys\Definitions\Tests\Sandbox;

use j0hnys\Definitions\Definition;

final class TestDefinition extends Definition {
    const schema = [
        'database' => [
            'factories' => [
                'Models' => 'T::integer()',
            ],
            'generated_migrations' => 'T::string()',
            'generated_model_exports' => 'T::string()',
            'generated_models' => 'T::string()',
        ]
    ];
}