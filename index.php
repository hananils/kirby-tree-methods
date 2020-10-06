<?php

require_once __DIR__ . '/lib/tree.php';

/**
 * Tree field method
 */
Kirby::plugin('hananils/kirby-tree-methods', [
    'fieldMethods' => [
        'toTree' => function ($field, $formatter = 'kirbytext') {
            if (!$formatter && !empty(option('hananils.tree.formatter'))) {
                $formatter = option('hananils.tree.formatter');
            }

            return new Hananils\Tree($field, $field->value(), $formatter);
        }
    ]
]);
