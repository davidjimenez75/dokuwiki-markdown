<?php
/*
 * Parenthesized UPPERCASE links:
 *   (CSV)         -> ([[:CSV]])
 *   (#CSV)        -> ([[:CSV|#CSV]])
 *   (CSV:TO-DO)   -> ([[:CSV:TO-DO|CSV:TO-DO]])
 *   (CSV--TO-DO)  -> ([[:CSV:TO-DO|CSV:TO-DO]])
 *   (CSV+TO-DO)   -> ([[:CSV:TO-DO|CSV:TO-DO]])
 *   (CSV/TO-DO)   -> ([[:CSV:TO-DO|CSV:TO-DO]])
 *
 * Lowercase tag patterns:
 *   (#tags)       -> ([[:tags|#tags]])
 *
 * Date patterns:
 *   (2026-03-23)           -> ([[:2026:2026-03-23]])
 *   (2026-03-23--1124)     -> ([[:2026:2026-03-23--1124]])
 *   (2026-03-23--112401)   -> ([[:2026:2026-03-23--112401]])
 *   (2026-03-23 01:02)     -> ([[:2026:2026-03-23--0102]])
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_parenlinks extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 103; }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '\((?:\d{4}-\d{2}-\d{2}(?:--\d{4,6}| \d{2}:\d{2})?|#?[A-Z][A-Z0-9:+/_-]*|#[a-z][a-z0-9_-]*)\)',
            $mode,
            'plugin_markdowku_parenlinks'
        );
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array(substr($match, 1, -1)); // strip outer parens
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode != 'xhtml') return false;

        $content = $data[0];

        // Date pattern: YYYY-MM-DD with optional time suffix
        if (preg_match('/^(\d{4})-\d{2}-\d{2}/', $content, $m)) {
            // Normalize " HH:MM" suffix to "--HHMM"
            $normalized = preg_replace('/ (\d{2}):(\d{2})$/', '--$1$2', $content);
            $renderer->doc .= '(';
            $renderer->internallink(':' . $m[1] . ':' . $normalized);
            $renderer->doc .= ')';
            return true;
        }

        // Non-date: detect optional leading # prefix
        $hash_prefix = ($content[0] === '#');
        if ($hash_prefix) {
            $content = substr($content, 1);
        }

        // Normalize separators to : (--- must come before --)
        $normalized = preg_replace('/---/', ':', $content);
        $normalized = preg_replace('/--/',  ':', $normalized);
        $normalized = preg_replace('/[+\/]/', ':', $normalized);

        $target = ':' . $normalized;
        $has_namespace = (strpos($normalized, ':') !== false);

        $renderer->doc .= '(';

        if ($hash_prefix) {
            $renderer->internallink($target, '#' . $normalized);
        } elseif ($has_namespace) {
            $renderer->internallink($target, $normalized);
        } else {
            $renderer->internallink($target, null);
        }

        $renderer->doc .= ')';

        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
