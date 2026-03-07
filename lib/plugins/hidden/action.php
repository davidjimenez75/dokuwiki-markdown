<?php
/**
 * action.php for Plugin hidden
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Guillaume Turri <guillaume.turri@gmail.com>
 */

if(!defined('DOKU_INC')) die();

class action_plugin_hidden extends Dokuwiki_Action_Plugin {
  function register(Doku_Event_Handler $controller) {
    $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array());
    $controller->register_hook('PARSER_WIKITEXT_PREPROCESS', 'BEFORE', $this, 'handle_obsidian_frontmatter', array());
  }

  /**
   * Inserts a toolbar button
   */
  function insert_button(&$event, $param) {
    $event->data[] = array (
        'type' => 'format',
        'title' => $this->getLang('button'),
        'icon' => '../../plugins/hidden/images/hidden.png',
        'open' => '<hidden>',
        'close' => '</hidden>',
        );
  }

  /**
   * Converts Obsidian-style FrontMatter delimiters (---) to the hidden plugin
   * syntax (<--->/</--->) before the wiki parser processes the page.
   *
   * Rules (matching Obsidian's FrontMatter spec):
   *  - The opening --- must be the very first line of the document.
   *  - The closing --- must appear within the first 100 lines.
   *  - Both delimiters must be exactly "---" (only optional trailing whitespace).
   *  - All other occurrences of "---" in the document are left untouched.
   */
  function handle_obsidian_frontmatter(&$event, $param) {
    $text = &$event->data;

    // Fast check: the document must begin with exactly "---" + newline.
    // Supports both Unix (\n) and Windows (\r\n) line endings.
    if (!preg_match('/^---[ \t]*\r?\n/', $text)) {
      return;
    }

    // Split on newlines while preserving \r so we can detect the line ending.
    $lines = explode("\n", $text);
    $useCRLF = (strpos($text, "\r\n") !== false);

    // Search for the closing "---" within the first 100 lines (skip line 0).
    $closeIndex = -1;
    $limit = min(100, count($lines));
    for ($i = 1; $i < $limit; $i++) {
      // rtrim to strip \r and any trailing spaces.
      if (rtrim($lines[$i]) === '---') {
        $closeIndex = $i;
        break;
      }
    }

    // No closing delimiter found within the allowed range — leave text as-is.
    if ($closeIndex === -1) {
      return;
    }

    // Replace the opening and closing delimiters.
    $lines[0] = $useCRLF ? "<--->\r" : '<--->';
    $lines[$closeIndex] = $useCRLF ? "</--->\r" : '</--->';

    $text = implode("\n", $lines);
  }
}

