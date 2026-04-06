<?php
/*
 * Replaces the [ ] (empty checkbox) pattern with the 🔲 emoji.
 * Handles the case that cannot be covered by entities.local.conf
 * because DokuWiki's linesToHash() splits on whitespace, making
 * a space-containing key like "[ ]" impossible to define there.
 *
 * Works in all inline contexts: list items, paragraphs, headers, etc.
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_taskpending extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 62; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '\[ \]',
            $mode,
            'plugin_markdowku_taskpending');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($state, $match);
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        $renderer->doc .= '🔲';
        return true;
    }
}
// ex: et ts=4 enc=utf-8 :
