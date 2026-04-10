<?php
/*
 * Obsidian/Markdown YAML frontmatter block rendered as a collapsible table.
 *
 *   ---
 *   aliases:
 *   author: David
 *   created: 2026-04-09
 *   tags:
 *     - #tag1
 *     - #tag2
 *   title: My Page
 *   ---
 *
 * Renders as:
 *   <details class="frontmatter">
 *     <summary>📋 Frontmatter</summary>
 *     <table class="frontmatter-table"> ... </table>
 *   </details>
 *
 * Rules:
 *   - Pattern requires at least one key: value line between the --- delimiters
 *     so plain --- horizontal rules are never mistaken for frontmatter.
 *   - List items (  - value) are joined inline under their parent key.
 *   - Sort 6: higher priority than hr.php (sort 8) to prevent --- being
 *     consumed as a horizontal rule before the full block is matched.
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_frontmatter extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'block'; }
    function getSort()  { return 6; }

    function connectTo($mode) {
        // Match YAML frontmatter:
        //   \n---\n              opening delimiter (DokuWiki prepends \n to every document)
        //   (?:                  one or more fields:
        //     [a-z][a-z0-9_]*:    key name
        //     [ \t]*[^\n]*\n      optional inline value + newline
        //     (?:[ \t]+-[ \t]     zero or more list items (  - value)
        //        [^\n]*\n)*
        //   )+
        //   ---                  closing delimiter
        $this->Lexer->addSpecialPattern(
            '\n---\n(?:[a-z][a-z0-9_]*:[ \t]*[^\n]*\n(?:[ \t]+-[ \t][^\n]*\n)*)+---',
            $mode,
            'plugin_markdowku_frontmatter'
        );
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        // Strip leading \n + opening ---\n  and closing ---
        $content = preg_replace('/^\n---\n/', '', $match);
        $content = preg_replace('/---$/', '', $content);

        $fields      = [];
        $current_key = null;

        foreach (explode("\n", $content) as $line) {
            if ($line === '') continue;

            if (preg_match('/^([a-z][a-z0-9_]*):\s*(.*)$/', $line, $m)) {
                $current_key          = $m[1];
                $fields[$current_key] = ['value' => trim($m[2]), 'items' => []];
            } elseif (preg_match('/^[ \t]+-[ \t]+(.*)$/', $line, $m) && $current_key !== null) {
                $fields[$current_key]['items'][] = trim($m[1]);
            }
        }

        return $fields;
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode !== 'xhtml') return false;
        if (empty($data))      return false;

        $html  = '<details class="frontmatter" style="background:transparent;border:none">';
        $html .= '<summary style="text-align:right;background:transparent;list-style:none">Frontmatter 📋</summary>';
        $html .= '<table class="frontmatter-table" style="background:transparent">';

        foreach ($data as $key => $field) {
            $html .= '<tr>';
            $html .= '<th>' . hsc($key) . '</th>';
            if (!empty($field['items'])) {
                if ($key === 'tags') {
                    $rendered = array_map([$this, 'renderTagItem'], $field['items']);
                    $html .= '<td>' . implode(' ', $rendered) . '</td>';
                } else {
                    $html .= '<td>' . implode(' ', array_map('hsc', $field['items'])) . '</td>';
                }
            } else {
                $value = hsc($field['value']);
                if ($key === 'title') $value = '<strong>' . $value . '</strong>';
                $html .= '<td>' . $value . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</details>';

        $renderer->doc .= $html;
        return true;
    }

    /**
     * Render a single frontmatter tag item as a root-level wiki link.
     *
     * Supported formats:
     *   #TAG   → <a href="?id=tag">#TAG</a>
     *   "tag"  → <a href="?id=tag">tag</a>
     *   tag    → <a href="?id=tag">tag</a>
     */
    private function renderTagItem(string $item): string {
        // Strip surrounding quotes: "tag" → tag
        if (preg_match('/^"(.*)"$/', $item, $m)) {
            $item = $m[1];
        }

        if ($item !== '' && $item[0] === '#') {
            // #TAG → strip # for link target, keep #TAG as display text
            $target  = cleanID(substr($item, 1));
            $display = hsc($item);
        } else {
            $target  = cleanID($item);
            $display = hsc($item);
        }

        $url = wl($target);
        return '<a href="' . hsc($url) . '" class="wikilink1">' . $display . '</a>';
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
