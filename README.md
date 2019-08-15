# HTML tree field methods

A Kirby 3 plugin providing field methods to filter and manipulate HTML output. It allows to adjust the headline hierarchie, change tag names, add classes and other attributes and to wrap multiple elements into another element. Filtering can be used to output specific elements only.

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

## Field Methods

Under the hood, the plugin converts the HTML string to `DomDocument` using `DomDocument->loadHTML()`. The content is expected to the encoded using UTF8. XPath is used to filter and find elements in the tree, see https://en.wikipedia.org/wiki/XPath#Syntax_and_semantics_(XPath_1.0).

### Selections

Given a field named `text`:

-   `$page->text()->select('xpath')` – return all elements matching the given xPath query.
-   `$page->text()->first()` – returns the first element of the selection.
-   `$page->text()->last()` – returns the last element of the selection.
-   `$page->text()->nth(5)` – returns the fiths element of the selection.
-   `$page->text()->limit(2)` – returns the first two elements of the selection.
-   `$page->text()->offset(1)` – return all elements but the first one of the selection.
-   `$page->text()->clear()` – clears the selection (select all again).

### Manipulations

Given a field named `text`:

-   `$page->text()->level(2)` – adjusts the headline hierachie to start at the given level.
-   `$page->text()->select('//strong')->setName('em')` – changes all `strong` elements to `em` elements.
-   `$page->text()->select('p')->setAttribute('class', 'example')` – adds an attributes to the selected elements.
-   `$page->text()->wrap('elementname', 'xpathfrom', 'xpathto', ['name' => 'value'])` – wraps all elements (from … to) in the given element, adding attributes if defined.

### Output

-   `$page->text()->html()` – returns the HTML of current selection.
-   `$page->text()->toDocument()` – returns the `DomDocument` object.
-   `$page->text()->toSelection()` – returns the `DomNodeList` of the current selection.

## Examples

### Adjusting Headline Hierarchy

```html
<h1>My first headline</h1>
<h2>My second headlins</h2>
```

```php
<?= $page->text()->level(3) ?>
```

```html
<h3>My first headline</h3>
<h4>My second headline</h4>
```

### Changing elements

```html
<p>This is <em>HTML</em>.</p>
```

```php
<?= $page->text()->select('//em[text() = "HTML"]')->setName('abbr')->setAttribute('title' => 'HyperText Markup Language')->clear()->html() ?>
```

```html
<p>This is <abbr title="HyperText Markup Language">HTML</abbr>.</p>
```

### Wrapping Elements

```html
<p><strong>Me:</strong> May I ask a question?</p>
<p><strong>You:</strong> Yes, of course, you may!</p>
<p>What's your question?</p>
<figure>
    <img src="overview.png" />
    <figcaption>A nice view</figcaption>
</figure>
```

```php
$page->text()->wrap('div', 'p[strong]', 'p[following-sibling::*[1][figcaption]]', ['class' => 'question-or-answer'])
```

```html
<div class="question-or-answer">
    <p><strong>Me:</strong> May I ask a question?</p>
</div>
<div class="question-or-answer">
    <p><strong>You:</strong> Yes, of course, you may!</p>
    <p>What's your question?</p>
</div>
<figure>
    <img src="overview.png" />
    <figcaption>A nice view</figcaption>
</figure>
```

## License

MIT

## Credits

-   [hana+nils · Büro für Gestaltung](https://hananils.de)
