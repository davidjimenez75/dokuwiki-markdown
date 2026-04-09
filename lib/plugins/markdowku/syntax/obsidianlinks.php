<?php
/*
 * Obsidian-style double bracket links:
 *   [[wiki/decisiones]]         -> internallink(':wiki:decisiones', 'wiki/decisiones')
 *   [[wiki/sub/page]]           -> internallink(':wiki:sub:page', 'wiki/sub/page')
 *   [[wiki/decisiones|My Link]] -> internallink(':wiki:decisiones', 'My Link')
 *   [[decisiones]]              -> internallink(':decisiones', 'decisiones')
 *   [[wiki/page#section]]       -> internallink(':wiki:page#section', 'wiki/page#section')
 *   [[./to-do|label]]           -> internallink('markdowku:to-do', 'label')   (page-as-folder relative)
 *   [[../other|label]]          -> internallink('other', 'label')              (parent of page-as-folder)
 *
 * Relative path resolution (Obsidian semantics: the current page acts as its own folder):
 *   From page  'markdowku'         -> ./to-do        resolves to  markdowku:to-do
 *   From page  'project:markdowku' -> ./to-do        resolves to  project:markdowku:to-do
 *   From page  'project:markdowku' -> ../sibling     resolves to  project:sibling
 *   From page  'markdowku'         -> ../sibling     resolves to  sibling  (root)
 *
 * Rules:
 *   - ./ prefix  -> treat current page ID as namespace prefix (resolved at render time via $ID)
 *   - ../ prefix -> go up one level from current page-as-folder (resolved at render time via $ID)
 *   - / is converted to : (Obsidian namespace separator -> DokuWiki)
 *   - Leading : is added to force absolute resolution (no namespace prepending)
 *   - Optional | separates path from display text
 *   - DokuWiki-native relative prefixes (~: .: ..:) and absolute links (:) are NOT intercepted
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_obsidianlinks extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 101; }

    function connectTo($mode) {
        // Exclude DokuWiki-native link prefixes so they pass through to the core parser:
        //   :    -> already absolute  (e.g. [[:page]])
        //   ~:   -> root-relative     (e.g. [[~:to-do]])
        //   .:   -> namespace-relative (e.g. [[.:page]])
        //   ..:  -> parent-namespace  (e.g. [[..:page]])
        $this->Lexer->addSpecialPattern(
            '\[\[(?!:|~:|\.\.?:)[^\]\n]+\]\]',
            $mode,
            'plugin_markdowku_obsidianlinks'
        );
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        // Strip [[ and ]]
        $inner = substr($match, 2, -2);

        // Split on | for optional display text
        $parts = explode('|', $inner, 2);
        $path  = trim($parts[0]);
        // Use explicit title if given, otherwise show the original path as written
        $title = isset($parts[1]) ? trim($parts[1]) : $path;

        // Detect Obsidian relative prefixes — resolve at render time using $ID
        if (strncmp($path, '../', 3) === 0) {
            $rest = str_replace('/', ':', substr($path, 3));
            return array('__PARENT__', $rest, $title);
        } elseif (strncmp($path, './', 2) === 0) {
            $rest = str_replace('/', ':', substr($path, 2));
            return array('__SELF__', $rest, $title);
        }

        // Absolute Obsidian path: convert / to : and force leading :
        $path = str_replace('/', ':', $path);
        if ($path[0] !== ':') {
            $path = ':' . $path;
        }

        return array('__ABS__', $path, $title);
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        global $ID;

        // Support legacy cached data (2-element array from old plugin format)
        if (count($data) < 3) {
            $renderer->internallink($data[0], $data[1]);
            return true;
        }

        list($type, $path, $title) = $data;

        if ($type === '__SELF__') {
            // Obsidian ./page — treat current page as its own folder
            // From 'markdowku'         -> markdowku:to-do
            // From 'project:markdowku' -> project:markdowku:to-do
            $path = $ID . ':' . $path;

        } elseif ($type === '__PARENT__') {
            // Obsidian ../page — go up one level from page-as-folder
            // From 'markdowku'         -> sibling  (root)
            // From 'project:markdowku' -> project:sibling
            $parts = explode(':', $ID);
            array_pop($parts); // remove page name (treat page as folder, so parent = its namespace)
            if (empty($parts)) {
                $path = $path; // already root-level
            } else {
                $path = implode(':', $parts) . ':' . $path;
            }
        }
        // __ABS__: path is already absolute, use as-is

        $renderer->internallink($path, $title);
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
