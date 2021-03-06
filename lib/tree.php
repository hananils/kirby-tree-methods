<?php

namespace Hananils;

use DomDocument;
use DOMXPath;

class Tree
{
    private $field;
    private $source;
    private $document;
    private $body;
    private $selection = null;
    private $query = null;
    private $errors = [];

    public function __construct($field, $source = null)
    {
        $this->field = $field;

        if ($source) {
            $this->source = $source;
        } elseif (isset($field::$methods['blocks'])) {
            $this->source = $field->blocks()->html();
        } elseif (isset($field::$methods['kirbytext'])) {
            $this->source = $field->kirbytext();
        } else {
            $this->source = $field->html();
        }

        if (empty($this->source)) {
            $this->source = '<html><head></head><body></body></html>';
        }

        $internal = libxml_use_internal_errors(true);

        $this->document = new DomDocument();
        $this->document->loadHTML('<?xml encoding="UTF-8">' . $this->source);
        $this->body = $this->document->documentElement->getElementsByTagName('body')->item(0);

        $this->errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($internal);
    }

    /**
     * Selections
     */

    public function first()
    {
        return $this->select('*[position() = 1]');
    }

    public function last()
    {
        return $this->select('*[position() = last()]');
    }

    public function nth($index)
    {
        return $this->select('*[position() = ' . $index . ']');
    }

    public function limit($limit = null)
    {
        return $this->select('*[position() <= ' . $limit . ']');
    }

    public function offset($offset = 0)
    {
        return $this->select('*[position() > ' . $offset . ']');
    }

    public function select($query)
    {
        if (!$this->query) {
            $this->query = '/html/body';
        }

        if (strpos($query, '/') === false) {
            $this->query .= '/';
        }

        $this->query .= $query;

        $xpath = new DOMXPath($this->document);
        $this->selection = $xpath->query($this->query);

        return $this;
    }

    public function clear()
    {
        $this->selection = null;
        $this->query = null;

        return $this;
    }

    /**
     * Manipulations
     */

    public function level($level = 1)
    {
        $level = intval($level);

        if ($level < 2) {
            return $this;
        }

        for ($i = 6; $i > 0; $i--) {
            $this->select('h' . $i);
            $this->setName('h' . min($i + $level - 1, 6));
            $this->clear();
        }

        return $this;
    }

    public function wrap($name, $from, $to = null, $attributes = [])
    {
        $xpath = new DOMXPath($this->document);
        $starters = $xpath->query('/html/body/' . $from);

        if (!$starters) {
            return $this;
        }

        if (!$to) {
            $to = $from;
        }

        // Create wrapper
        $element = $this->document->createElement($name);
        foreach ($attributes as $attribute => $value) {
            $element->setAttribute($attribute, $value);
        }

        // Loop over starters
        foreach ($starters as $node) {
            $wrapper = $element->cloneNode();
            $node->parentNode->insertBefore($wrapper, $node);

            while ($wrapper->nextSibling) {
                $current = $wrapper->nextSibling;

                if ($wrapper->hasChildNodes() && $xpath->query('self::' . $from, $current)->length) {
                    break;
                }

                $wrapper->appendChild($current);

                $next = $xpath->query('self::' . $to, $current);
                if (!$next || $next->length) {
                    break;
                }
            }
        }

        return $this;
    }

    public function wrapText($needle, $name, $attributes = [], $word = true)
    {
        $xpath = new DOMXPath($this->document);
        $matches = $xpath->query('/html/body//*[text()[contains(.,"' . $needle . '")]]/text()');

        // Create wrapper
        $element = $this->document->createElement($name);
        foreach ($attributes as $attribute => $value) {
            $element->setAttribute($attribute, $value);
        }

        // Loop over text nodes
        foreach ($matches as $node) {
            if ($word === true) {
                $parts = preg_split('/\b' . $needle . '\b/', $node->textContent);
            } else {
                $parts = explode($needle, $node->textContent);
            }

            for ($i = 0; $i < count($parts); $i++) {
                $textnode = $this->document->createTextNode($parts[$i]);
                $node->parentNode->insertBefore($textnode, $node);

                if ($i < count($parts) - 1) {
                    $wrapper = $element->cloneNode();
                    $wrapper->textContent = $needle;
                    $node->parentNode->insertBefore($wrapper, $node);
                }
            }

            $node->parentNode->removeChild($node);
        }

        return $this;
    }

    public function setName($name)
    {
        $i = $this->selection->length - 1;
        while ($i > -1) {
            $oldNode = $this->selection->item($i);
            $newNode = $oldNode->ownerDocument->createElement($name);

            if ($oldNode->attributes->length) {
                foreach ($oldNode->attributes as $attribute) {
                    $newNode->setAttribute($attribute->nodeName, $attribute->nodeValue);
                }
            }

            while ($oldNode->firstChild) {
                $newNode->appendChild($oldNode->firstChild);
            }

            $oldNode->parentNode->replaceChild($newNode, $oldNode);

            $i--;
        }

        if ($this->query) {
            $this->select($this->query);
        }

        return $this;
    }

    public function setAttribute($name, $value)
    {
        $i = $this->selection->length - 1;
        while ($i > -1) {
            $node = $this->selection->item($i);
            $node->setAttribute($name, $value);

            $i--;
        }

        return $this;
    }

    /**
     * Utilities
     */

    private function getNodes($clear = false)
    {
        $nodes = $this->body->childNodes;

        if (!$clear && isset($this->selection) && $this->selection->length) {
            $nodes = $this->selection;
        }

        return $nodes;
    }

    /**
     * Output
     */

    public function html($clear = false)
    {
        if (!empty($this->error)) {
            return $this->source;
        }

        $html = '';
        foreach ($this->getNodes($clear) as $node) {
            $html .= $this->document->saveHTML($node);
        }

        return $html;
    }

    public function content($clear = false)
    {
        if (!empty($this->error)) {
            return $this->source;
        }

        $content = '';
        foreach ($this->getNodes($clear) as $node) {
            foreach ($node->childNodes as $children) {
                $content .= $this->document->saveHTML($children);
            }
        }

        return $content;
    }

    public function text($clear = false)
    {
        if (!empty($this->error)) {
            return $this->source;
        }

        $text = '';
        foreach ($this->getNodes($clear) as $node) {
            $text .= $node->textContent;
        }

        return $text;
    }

    public function toDocument()
    {
        return $this->document;
    }

    public function toSelection()
    {
        return $this->selection;
    }

    public function toField($converter = 'html')
    {
        $this->field->value = $this->{$converter}();
        return $this->field;
    }

    public function __toString()
    {
        return (string) $this->html();
    }
}
