<?php
/*
 * Obsidian-style double bracket links:
 *   [[wiki/decisiones]]         -> internallink(':wiki:decisiones', 'wiki/decisiones')
 *   [[wiki/sub/page]]           -> internallink(':wiki:sub:page', 'wiki/sub/page')
 *   [[wiki/decisiones|My Link]] -> internallink(':wiki:decisiones', 'My Link')
 *   [[decisiones]]              -> internallink(':decisiones', 'decisiones')
 *   [[wiki/page#section]]       -> internallink(':wiki:page#section', 'wiki/page#section')
 *
 * Rules:
 *   - / is converted to : (Obsidian namespace separator -> DokuWiki)
 *   - Leading : is added to force absolute resolution (no namespace prepending)
 *   - Optional | separates path from display text
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_obsidianlinks extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 101; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '\[\[[^\]\n]+\]\]',
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

        // Convert / to : (Obsidian separator -> DokuWiki namespace separator)
        $path = str_replace('/', ':', $path);

        // Make absolute with leading : to prevent namespace-relative resolution
        if ($path[0] !== ':') {
            $path = ':' . $path;
        }

        return array($path, $title);
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        $renderer->internallink($data[0], $data[1]);
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
