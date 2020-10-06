<?php

require_once __DIR__ . '/lib/tree.php';

/**
 * Tree field method
 */
Kirby::plugin('hananils/kirby-tree-methods', [
    'fieldMethods' => [
        'toTree' => function ($field, $source = null, $formatter = false) {
            if (!$formatter && !empty(option('hananils.tree.formatter'))) {
                $formatter = option('hananils.tree.formatter');
            }

            if ($formatter) {
                return new Hananils\Tree($field, $source, $formatter);
            } else {
                return new Hananils\Tree($field, $source);
            }
        }
    ]
]);
