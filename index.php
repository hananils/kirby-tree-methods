<?php

require_once __DIR__ . '/lib/tree.php';

/**
 * Tree field method
 */
Kirby::plugin('hananils/tree-methods', [
    'fieldMethods' => [
        'toTree' => function ($field, $formatter = null) {
            if (!$formatter && !empty(option('hananils.tree.formatter'))) {
                $formatter = option('hananils.tree.formatter');
            }

            if ($formatter === 'toBlocks') {
                $source = $field->toBlocks()->toHtml();
            } elseif ($formatter) {
                $source = $field->{$formatter}();
            } else {
                $source = $field->value();
            }

            return new Hananils\Tree($field, $source);
        }
    ]
]);
