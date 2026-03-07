# Markdown


**Links ⭐**

- markdowku Plugin -- https://www.dokuwiki.org/plugin:markdowku


----
# Example of standard Markdown syntax to test DokuWiki Markdown support (CHATGPT--5.3)

#### Purpose

A single long Markdown document containing the most common Markdown constructs.  
Useful for verifying compatibility, parsing behavior, and rendering differences in systems such as Dokuwiki.

---

#### Headers

# H1 Header

## H2 Header

### H3 Header

#### H4 Header

##### H5 Header

###### H6 Header

---

#### Paragraphs and Line Breaks

This is a normal paragraph written in Markdown.  
Line breaks can be created with two spaces at the end of a line.

This is another paragraph separated by a blank line.

---

#### Text Formatting

*Italic text*

_Italic alternative_

**Bold text**

__Bold alternative__

**Bold and italic**

~~Strikethrough~~

---

#### Lists

##### Unordered list

- Item one
- Item two
- Item three
- Nested list
  - Nested item A
  - Nested item B

##### Ordered list

1. First item
2. Second item
3. Third item
4. Nested ordered
   1. Sub item
   2. Sub item

---

#### Links

Inline link

[Markdown Guide](https://www.markdownguide.org)

Automatic link

<https://example.com>

---

#### Images

![Example Image](https://via.placeholder.com/150)

---

#### Blockquotes

> This is a blockquote.
>
> It can contain multiple lines.
>
>> Nested blockquote.

---

#### Horizontal Rules

---

***

___

---

#### Inline Code

Use the `printf()` function in C.

Example command:

`ls -la`

---

#### Code Blocks

```bash
echo "Hello world"
ls -la
````

```php
<?php

function hello($name) {
return "Hello " . $name;
}

echo hello("world");

?>
```

```json
{
"name": "markdown-test",
"version": "1.0",
"enabled": true
}
```

---

#### Tables

| Feature | Supported | Notes                 |
| ------- | --------- | --------------------- |
| Headers | Yes       | Basic syntax          |
| Lists   | Yes       | Nested lists may vary |
| Tables  | Partial   | Depends on parser     |

---

#### Task Lists (GitHub style)

* [x] Install Markdown parser
* [x] Create test document
* [ ] Validate Dokuwiki rendering
* [ ] Fix unsupported features

---

#### Definition Lists (not standard everywhere)

Term 1
: Definition of term 1

Term 2
: Definition of term 2

---

#### Escaping Characters

*This text is not italic*

# This is not a header

---

#### HTML Inside Markdown

<div style="border:1px solid #ccc;padding:10px;">
HTML can sometimes be embedded inside Markdown.
</div>

---

#### Footnotes (extended Markdown)

Here is a sentence with a footnote.[^1]

[^1]: This is the footnote content.

---

#### Emoji (extended Markdown)

:smile:
:rocket:
:warning:

---

#### Mixed Example

# Example Document

This is a **sample Markdown document** that includes:

* lists
* `inline code`
* tables
* links
* blockquotes

> Markdown is designed to be easy to read and easy to write.

Example code:

```python
def hello():
print("hello markdown")
```

---

#### Raw Text Stress Test

Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.

* Ut enim ad minim veniam
* Quis nostrud exercitation
* Ullamco laboris nisi ut aliquip ex ea commodo consequat

| Column A | Column B |
| -------- | -------- |
| Value 1  | Value 2  |
| Value 3  | Value 4  |

---

#### Citations

* [https://www.markdownguide.org/basic-syntax/](https://www.markdownguide.org/basic-syntax/)
* [https://daringfireball.net/projects/markdown/syntax](https://daringfireball.net/projects/markdown/syntax)
* [https://commonmark.org/help/](https://commonmark.org/help/)

