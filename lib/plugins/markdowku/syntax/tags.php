<?php
/*
 * Inline #TAG links (uppercase only):
 *   #TAG                  -> [[TAG|#TAG]]
 *   #TAG-WITH-HYPHENS     -> [[TAG-WITH-HYPHENS|#TAG-WITH-HYPHENS]]
 *   #TAG_WITH_UNDERSCORES -> [[TAG_WITH_UNDERSCORES|#TAG_WITH_UNDERSCORES]]
 *
 * Rules:
 *   - Must start with an uppercase letter after #
 *   - Allowed characters: A-Z, 0-9, hyphens (-), underscores (_)
 *   - Not triggered inside code spans, code blocks, or [[...]] links
 *   - Link target strips # prefix (Obsidian-style, no leading :)
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_tags extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 104; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '#[A-Z][A-Z0-9_-]*',
            $mode,
            'plugin_markdowku_tags'
        );
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array(substr($match, 1)); // strip leading #
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode != 'xhtml') return false;

        $tag = $data[0];
        $renderer->internallink(':' . $tag, '#' . $tag);
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
