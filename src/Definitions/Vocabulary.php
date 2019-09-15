<?php

namespace j0hnys\Definitions\Definitions;

final class Vocabulary
{
    const schema = [
        "reference" => [
            'property' => '/{{\w+}}/',
            'ontology' => '/^@\\[\w\\]+/'
        ],
        'type' => '/T::\w+\(\)/',
        'function_execution' => '^@\\[\w\\]+:\w+(\(({{\w+?}})+\))?'
    ];    
}

