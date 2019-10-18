<?php

load([
    'Hananils\\Tree' => 'lib/tree.php'
], __DIR__);

Kirby::plugin('hananils/kirby-tree-methods', [
    'fieldMethods' => [
        'toTree' => function ($field, $source = null) {
            return new Hananils\Tree($field, $source);
        }
    ]
]);
