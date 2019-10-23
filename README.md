# Definitions

Definitions are models of implementation. For more info on the theory see [here](http://johnstamkos.com/2019/10/19/definitions/)

# Example

Having the Definition:
```php
<?php

namespace App;

use j0hnys\Definitions\Definition;

final class Test extends Definition {
    const schema = [
        'database' => [
            'factories' => [
                'Models' => 'T::integer()',
            ],
            'generated_migrations' => 'T::string()',
            'generated_model_exports' => 'T::string()',
            'generated_models' => 'T::string()',
        ],
    ];
}
```

We can make the following checks that **pass**:

```php
$test = new Test();

$test->checkPath('database/factories/Models/*');

$test->check([
    'database' => [
        'factories' => [
            'Models' => 1,
        ],
        'generated_migrations' => 'some',
        'generated_model_exports' => 'thing',
        'generated_models' => 'else',
    ],
]);
```