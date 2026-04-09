---
title: HIDDEN--obsidian compatible metatags testing with Dokuwiki
tags:
  - #dokuwiki
  - #extensions
  - #metatags
  - #php
  - #wiki
category: metatags
status: wip
updated: 2026-03-07
---
**[[:Hidden|Hidden:frontmatter]]**


### Using Frontmatter in Obsidian to Manage Note Metadata (CHATGPT--5.3)

#### What Frontmatter Is

Frontmatter is a **metadata block written in YAML** placed at the beginning of a note in Obsidian.  
It allows you to store structured information that can be used for:

- organization
- filtering and searching
- plugin automation
- data queries
- note classification

Frontmatter is widely used in systems based on **Markdown knowledge bases** and static content systems.

In Obsidian, the Frontmatter block must appear **at the very top of the file** and be surrounded by three dashes.


---

## metadata

Everything between those markers is interpreted as structured data.

#### Basic Structure

A minimal Frontmatter block looks like this:

```
title: My Note
tags: [programming, php]
created: 2026-03-07
```

After this block, the normal Markdown content of the note begins.

Example:

```yaml
title: My First Obsidian Note
tags: [learning, obsidian]
created: 2026-03-07
status: draft
```


# Introduction

This is the main content of the note.


#### Common Fields Used in Frontmatter

Frontmatter fields are flexible and user-defined, but some common ones include:

| Field   | Purpose                                  |
| ------- | ---------------------------------------- |
| title   | Title of the note                        |
| tags    | Tags associated with the note            |
| created | Creation date                            |
| updated | Last modification date                   |
| status  | State of the note (draft, final, review) |
| aliases | Alternative names for linking            |
| author  | Author of the note                       |

Example:

```yaml
title: PHP Learning Notes
tags:
- programming
- php
- backend
created: 2026-03-01
updated: 2026-03-07
status: active
author: David
```

#### Lists in Frontmatter

Lists can be defined in two ways.

Inline format:

```yaml
tags: [php, backend, api]
```

Block format:

```yaml
tags:
- php
- backend
- api
```

Both formats are valid YAML.

#### Using Tags

Tags defined in Frontmatter behave the same as inline tags in the note body.

Example:

```yaml
tags: [linux, debian, server]
```

These tags will appear in the Obsidian tag system.

#### Aliases for Note Linking

Aliases allow a note to be referenced by multiple names.

Example:

```yaml
aliases:
- Airflow
- Apache Airflow Guide
```

This allows links like:

```
[[Airflow]]
```

to point to the same note.

#### Boolean and Numeric Values

Frontmatter supports other data types.

Boolean:

```yaml
published: true
```

Number:

```yaml
priority: 1
```

#### Dates

Dates should follow a consistent format, typically ISO format.

```yaml
created: 2026-03-07
```

or

```yaml
created: 2026-03-07T10:30
```

#### Nested Data Structures

YAML allows hierarchical structures.

Example:

```yaml
project:
name: AI Research
status: active
team:
- Alice
- Bob
- Carol
```

#### Example of Complete Frontmatter

```yaml
title: Apache Airflow Overview
aliases:
- Airflow
tags:
- data-engineering
- workflow
- automation
created: 2026-02-10
updated: 2026-03-07
status: review
priority: 2
author: David
related:
- [[Python]]
- [[ETL]]
```

#### Best Practices

Use consistent field names across notes.

Keep metadata minimal but useful.

Recommended common structure:

```yaml
title:
tags:
created:
updated:
status:
aliases:
```

Avoid excessive metadata that is never queried.

#### Advantages of Using Frontmatter

Pros

* structured metadata inside Markdown notes
* easier filtering and searching
* powerful integration with plugins
* automation possibilities
* improved knowledge organization

Cons

* requires YAML syntax knowledge
* inconsistent fields can reduce usefulness
* large metadata blocks may clutter notes

#### Typical Use Cases

Frontmatter is commonly used for:

* knowledge management systems
* personal wikis
* project documentation
* research notes
* technical documentation
* Zettelkasten workflows

It becomes especially powerful when combined with plugins that query metadata.

#### Citations

* [https://help.obsidian.md/properties](https://help.obsidian.md/properties)
* [https://yaml.org/spec](https://yaml.org/spec)
* [https://www.markdownguide.org/basic-syntax](https://www.markdownguide.org/basic-syntax)






