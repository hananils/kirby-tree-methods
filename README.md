![Kirby Tree Methods](.github/title.png)

**Tree Methods** is a plugin for [Kirby 3](https://getkirby.com) to convert field values to HTML and adapt the resulting markup by changing headline hierarchy, wrapping and filtering elements, manipulating tag names, add classes or other attributes. The main purpose of the plugin is to take the field markup, that usually is a static string returned by a formatter, and help web designers to tailor it the way they need it and to apply typographic tweaks. Therefor is allows for:

1. [selecting and filtering content](),
2. [manipulating the resulting markup](),
3. [outputing HTML]().

The plugin works with all Kirby fields that allow for the generation of valid HTML, like **Blocks** or **textareas**. It also accepts custom methods to convert field values to HTML.

## Examples

### Manipulating the headline hierarchy

Content:

```markdown
Text:

# My first headline

## My second headline
```

Template:

```php
// Set the starting headline level to three,
// making the document start as `h3` instead of `h1`
<?= $page->text()->toTree()->level(3) ?>
```

Output:

```html
<h3>My first headline</h3>
<h4>My second headline</h4>
```

### Manipulating markup using snippets

Content:

```markdown
Text:

These are Markdown paragraphs that an editor wrote that needs to be wrapped with special markup.

But the editor shouldn't have to care about markup in his Markdown document.
```

Template:

```php
<?= $page->text()->toTree()->snippets('elements') ?>
```

Snippets `/site/snippets/elements/p.php`:

```php
<div id="wrapper">
    <p<?=e($position === 1, 'class="intro"')?>>
        <?= $content ?>
    </p>
</div>
```

Output:

```php
<div id="wrapper">
    <p class="intro">These are Markdown paragraphs that an editor wrote that needs to be wrapped with special markup.</p>
</div>
<div id="wrapper">
    <p>But the editor shouldn't have to care about markup in his Markdown document.</p>
</div>
```

### Changing elements

Field value:

```markdown
This is _HTML_.
```

Template:

```php
<?= $page
    ->text()
    ->toTree()
    ->select('//em[text() = "HTML"]')
    ->setName('abbr')
    ->setAttribute('title' => 'HyperText Markup Language')
    ->clear()
    ->html() ?>
```

Output:

```html
<p>This is <abbr title="HyperText Markup Language">HTML</abbr>.</p>
```

### Wrapping Elements

Field value:

```markdown
Text:

**Me:** May I ask a question?<

**You:** Yes, of course, you may!

What's your question?
```

Template:

```php
// Group elements, starting each group with a `strong` element
$page
    ->text()
    ->toTree()
    ->wrap()
        'div',
        'p[strong]',
        'p[following-sibling::*[1][strong]]',
        [
            'class' => 'question-or-answer',
        ]
    );
```

Output:

```html
<div class="question-or-answer">
    <p><strong>Me:</strong> May I ask a question?</p>
</div>
<div class="question-or-answer">
    <p><strong>You:</strong> Yes, of course, you may!</p>
    <p>What's your question?</p>
</div>
```

## Installation

### Download

Download and copy this repository to `/site/plugins/tree-methods`.

### Git submodule

```
git submodule add https://github.com/hananils/kirby-tree-methods.git site/plugins/tree-methods
```

### Composer

```
composer require hananils/kirby-tree-methods
```

# Field Methods

Under the hood, the plugin converts the HTML string to `DomDocument` using `DomDocument->loadHTML()`. The content is expected to the encoded using UTF8. XPath is used to filter and find elements in the tree, see https://en.wikipedia.org/wiki/XPath#Syntax_and_semantics_(XPath_1.0).

### `toTree($formatter)`

Converts the field value to a DOM tree using the given formatter.

-   **`$formatter`:** a field method to convert the field content to HTML, e. g. `kirbytext` or `toBlocks`. Defaults to `kirbytext`.

A global default can be set by defining `hananils.tree.formatter` in the configuration.

## Selections

Given a field named `text` and `$tree = $page->text()->toTree()`:

### `$tree->select($xpath)`

Selects all elements matching the given xPath query.

-   **`$xpath`:** xPath query, see <https://en.wikipedia.org/wiki/XPath>

```php
// Select all paragraphs
$tree->select('p');
```

### `$tree->first()`

Selects the first element.

### `$tree->last()`

Selects the last element.

### `$tree->nth($position)`

Selects the nth element.

-   **`$position`:** position of the element to

### `$tree->limit($number)`

Limits the current selection to the given number.

-   **`$number`:** the limit.

### `$tree->offset(1)`

Offsets the current selection by the given number.

-   **`$number`:** the offset.

### `$tree->clear()`

Clears the current selection and selects all nodes again (keeping all manipulations).

### `$isEmpty()`

Returns true is the current selection is empty.

### `$isNotEmpty()`

Returns true is the current selection is not empty.

### `has($query)`

Returns true is the current selection contains the given element.

-   **`$query`:** an xPath query, can be as simple as the name of an element.

### `count()`

Counts the nodes in the current selection.

## Manipulations

Given a field named `text` and `$tree = $page->text()->toTree()`:

### `$tree->level($start)`

Adjusts the headline hierarchy to start at the given level.

-   **`$start`:** the level to start headlines with.

```php
// Make all `h1` a `h3`, all `h2` a `h4` …
$tree->level(3);
```

### `$tree->select($xpath)->setName($name)`

Selects all elements matching the given xPath query and updates the element names.

-   **`$xpath`:** xPath query, see <https://en.wikipedia.org/wiki/XPath>
-   **`$name`:** the new element name.

```php
// Rename all `strong` to `em`
$tree->select('//strong')->setName('em');
```

### `$tree->select($xpath)->setAttribute($attribute, $value)`

Selects all elements matching the given xPath query and sets the given attribute.

-   **`$xpath`:** xPath query, see <https://en.wikipedia.org/wiki/XPath>
-   **`$attribute`:** the attribute name.
    . **`$value`:** the attribute value.

```php
// Set the class `example` to all paragraphs
$tree->select('p')->setAttribute('class', 'example');
```

### `$tree->wrap($element, $from, $name, $attributes)`

Wraps all elements (from … to) in the given element, adding attributes if defined.

-   **`$element`:** name of the wrapping element.
-   **`$from`:** xPath query for the start element, see <https://en.wikipedia>.
-   **`$to`:** xPath query for the end element, see <https://en.wikipedia>.
-   **`$attributes`:** array of attributes to be set on the wrapping element.

```php
$tree->wrap('div', 'h1', 'h2', ['class' => 'example']);
```

### `$tree->wrapText($string, $element, $attributes)`

Wraps all text occurences of the string in the given element, adding attributes if defined.

-   **`$string`:** string to search for.
-   **`$element`:** name of the wrapping element.
-   **`$attributes`:** array of attributes to be set on the wrapping element.

```php
$tree->wrapText('Kirby', 'strong', ['class' => 'is-great']);
```

### `snippets($path, $data)`

Apply a snippet to each element in the current selection. Looks for a snippet with the name of the element first, uses a snippet named `default` second or leaves the element unchanged if none is found.

-   **`$path`:** path inside the snippet folder to look for the element snippets.
-   **`$data`:** additional data that should be passed to the snippet.

By default, each snippet gets this data passed:

-   **`$parent`:** the field parent, e. g. `$page` or `$site`.
-   **`$field`:** the field.
-   **`$node`:** the DOM node.
-   **`$content`:** the inner HTML of the element.
-   **`$attrs`:** the existing attributes of the element.
-   **`$next`:** the next element.
-   **`$prev`:** the previous element.
-   **`$position`:** the position of the current element.
-   **`$positionOfType`:** the position of the current element compared to elements of the same type.

See example in the introduction.

### `widont()`

Improved version of the Kirby method, taking the DOM into account.

### `excerpt()`

Alias for the Kirby method.

### `kirbytextinline()`

Improved version of the Kirby method, taking the DOM into account.

### `smartypants()`

Improved version of the Kirby method, taking the DOM into account.

## Output

Given a field named `text` and `$tree = $page->text()->toTree()`:

### `$tree->html($clear)`

Returns the HTML of the current selection.

-   **`$clear`:** boolean flag whether to clear the current selection, defaults to `false`.

### `$tree->content($clear)`

Returns the content of the current selection (text and child nodes).

-   **`$clear`:** boolean flag whether to clear the current selection, defaults to `false`.

### `$tree->text($clear)`

Returns the text value of the current selection.

-   **`$clear`:** boolean flag whether to clear the current selection, defaults to `false`.

### `$tree->toDocument()`

Returns the `DomDocument` object.

### `$tree->toSelection()`

Returns the `DomNodeList` of the current selection.

### `$tree->toField($type)`

Returns the field instance with the field value set to the current html (default), content or text value.

-   **`$type`:** the returned content type, either `html`, `content` or `text`.

# License

This plugin is provided freely under the [MIT license](LICENSE.md) by [hana+nils · Büro für Gestaltung](https://hananils.de).  
We create visual designs for digital and analog media.
