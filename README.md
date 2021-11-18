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

## Selections

Given a field named `text` and `$tree = $page->text()->toTree()`:

-   `$tree->select('xpath')` – return all elements matching the given xPath query.
-   `$tree->first()` – returns the first element of the selection.
-   `$tree->last()` – returns the last element of the selection.
-   `$tree->nth(5)` – returns the fiths element of the selection.
-   `$tree->limit(2)` – returns the first two elements of the selection.
-   `$tree->offset(1)` – return all elements but the first one of the selection.
-   `$tree->clear()` – clears the selection (select all again).

## Manipulations

Given a field named `text` and `$tree = $page->text()->toTree()`:

-   `$tree->level(2)` – adjusts the headline hierachie to start at the given level.
-   `$tree->select('//strong')->setName('em')` – changes all `strong` elements to `em` elements.
-   `$tree->select('p')->setAttribute('class', 'example')` – adds an attributes to the selected elements.
-   `$tree->wrap('elementname', 'xpathfrom', 'xpathto', ['name' => 'value'])` – wraps all elements (from … to) in the given element, adding attributes if defined.
-   `$tree->wrapText('Kirby', 'strong', ['class' => 'is-great'])` – wraps all text occurences of 'Kirby' in a `strong` element with the class `is-great`.

## Output

Given a field named `text` and `$tree = $page->text()->toTree()`:

-   `$tree->html()` – returns the HTML of the current selection.
-   `$tree->content()` – returns the content of the current selection (text and child nodes).
-   `$tree->text()` – returns the text value of the current selection.
-   `$tree->toDocument()` – returns the `DomDocument` object.
-   `$tree->toSelection()` – returns the `DomNodeList` of the current selection.
-   `$tree->toField('html|content|text')` – returns the field instance with the field value set to the current html (default), content or text value.

# License

This plugin is provided freely under the [MIT license](LICENSE.md) by [hana+nils · Büro für Gestaltung](https://hananils.de).  
We create visual designs for digital and analog media.
