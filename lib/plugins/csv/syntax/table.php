<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * CSV Plugin: displays a cvs formatted file or inline data as a table
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Steven Danz <steven-danz@kc.rr.com>
 * @author     Gert
 * @author     Andreas Gohr <gohr@cosmocode.de>
 * @author     Jerry G. Geiger <JerryGeiger@web.de>
 */
/**
 * Display CSV data as table
 */
class syntax_plugin_csv_table extends SyntaxPlugin
{
    /** @inheritdoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 155;
    }

    /** @inheritdoc */
    public function getPType()
    {
        return 'block';
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('<csv[^>]*>.*?(?:<\/csv>)', $mode, 'plugin_csv_table');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $match = substr($match, 4, -6); // <csv ... </csv>

        [$optstr, $content] = explode('>', $match, 2);
        $opt = helper_plugin_csv::parseOptions($optstr);
        $opt['content'] = $content;

        return $opt;
    }

    /** @inheritdoc */
    public function render($mode, Doku_Renderer $renderer, $opt)
    {
        if ($mode == 'metadata') return false;

        // load file data
        if ($opt['file']) {
            try {
                $opt['content'] = helper_plugin_csv::loadContent($opt['file']);
                if (!media_ispublic($opt['file'])) $renderer->info['cache'] = false;
            } catch (\Exception $e) {
                $renderer->cdata($e->getMessage());
                return true;
            }
        }

        // check if there is content
        $content =& $opt['content'];
        $content = trim($content);
        if ($content === '') {
            $renderer->cdata('No csv data found');
            return true;
        }

        $data = helper_plugin_csv::prepareData($content, $opt);

        if (empty($data)) {
            $message = $this->getLang('no_result');
            $renderer->cdata($message);
            return true;
        }

        $maxcol = count($data[0]);
        $line = 0;

        // render
        $renderer->table_open($maxcol, count($data));
        // Open thead or tbody
        ($opt['hdr_rows']) ? $renderer->tablethead_open() : $renderer->tabletbody_open();
        foreach ($data as $row) {
            // close thead yet?
            if ($line > 0 && $line == $opt['hdr_rows']) {
                $renderer->tablethead_close();
                $renderer->tabletbody_open();
            }
            $renderer->tablerow_open();
            for ($i = 0; $i < $maxcol;) {
                $span = 1;
                // lookahead to find spanning cells
                if ($opt['span_empty_cols']) {
                    for ($j = $i + 1; $j < $maxcol; $j++) {
                        if ($row[$j] === '') {
                            $span++;
                        } else {
                            break;
                        }
                    }
                }

                // open cell
                if ($line < $opt['hdr_rows'] || $i < $opt['hdr_cols']) {
                    $renderer->tableheader_open($span);
                } else {
                    $renderer->tablecell_open($span);
                }

                // print cell content, call linebreak() for newlines
                $lines = explode("\n", $row[$i]);
                $cnt = count($lines);
                for ($k = 0; $k < $cnt; $k++) {
                    $this->renderInline($renderer, $lines[$k]);
                    if ($k < $cnt - 1) $renderer->linebreak();
                }

                // close cell
                if ($line < $opt['hdr_rows'] || $i < $opt['hdr_cols']) {
                    $renderer->tableheader_close();
                } else {
                    $renderer->tablecell_close();
                }

                $i += $span;
            }
            $renderer->tablerow_close();
            $line++;
        }
        // if there was a tbody, close it
        if ($opt['hdr_rows'] < $line) $renderer->tabletbody_close();
        $renderer->table_close();

        return true;
    }

    /**
     * Convert Obsidian-style links to DokuWiki internal links before parsing.
     *
     * Examples:
     *   [[CSV]]          -> [[:CSV]]
     *   [[CSV/TO-DO]]    -> [[:CSV:TO-DO]]
     *   [[CSV/TO-DO/1]]  -> [[:CSV:TO-DO:1]]
     *   [[CSV|label]]    -> [[:CSV|label]]
     *
     * Leaves alone:
     *   [[:CSV]]         already DokuWiki format
     *   [[https://...]]  external URL link
     *
     * @param string $text
     * @return string
     */
    protected function convertObsidianLinks(string $text): string
    {
        // Convert Obsidian [[Page/Sub]] to DokuWiki [[:Page:Sub|Page:Sub]]
        $text = preg_replace_callback(
            '/\[\[(?![:\s])(?!https?:\/\/)([^\]|]+?)(\|[^\]]+)?\]\]/',
            function ($m) {
                $path = str_replace('/', ':', $m[1]);
                $alias = $m[2] ?? '';
                // For multi-level paths with no explicit alias, show the full path as label
                if ($alias === '' && strpos($m[1], '/') !== false) {
                    $alias = '|' . $path;
                }
                return '[[:' . $path . $alias . ']]';
            },
            $text
        );

        // For already-DokuWiki multi-level links [[:Page:Sub]] with no alias, show the full path as label
        $text = preg_replace_callback(
            '/\[\[:([\w.-]+(?::[\w.-]+)+?)(\|[^\]]+)?\]\]/',
            function ($m) {
                $path = $m[1];
                $alias = $m[2] ?? '';
                if ($alias === '') {
                    $alias = '|' . $path;
                }
                return '[[:' . $path . $alias . ']]';
            },
            $text
        );

        return $text;
    }

    /**
     * Convert date-inside-parentheses to DokuWiki internal links under the year namespace.
     * Must run before convertTagLinks() to prevent double-dash date suffixes being misprocessed.
     *
     * Examples:
     *   (2026-03-23)           -> [[:2026:2026-03-23]]
     *   (2026-03-23--0102)     -> [[:2026:2026-03-23--0102]]
     *   (2026-03-23--010203)   -> [[:2026:2026-03-23--010203]]
     *   (2026-03-23 01:02)     -> [[:2026:2026-03-23--0102]]
     *
     * @param string $text
     * @return string
     */
    protected function convertDateLinks(string $text): string
    {
        return preg_replace_callback(
            '/\((\d{4}-\d{2}-\d{2})(?:--(\d{4,6})| (\d{2}):(\d{2}))?\)/',
            function ($m) {
                $date = $m[1];
                $year = substr($date, 0, 4);
                if (!empty($m[2])) {
                    // (2026-03-23--0102) or (2026-03-23--010203)
                    $slug = $date . '--' . $m[2];
                } elseif (!empty($m[3])) {
                    // (2026-03-23 01:02) -> 2026-03-23--0102
                    $slug = $date . '--' . $m[3] . $m[4];
                } else {
                    // (2026-03-23)
                    $slug = $date;
                }
                return '([[:' . $year . ':' . $slug . ']])';
            },
            $text
        );
    }

    /**
     * Convert (TAG)-style content to DokuWiki internal links.
     *
     * Rules:
     *   - Leading # is stripped
     *   - Two or more dashes (--, ---) become namespace separator (:)
     *   - Plus (+) becomes namespace separator (:)
     *   - Colon (:) is already a namespace separator
     *   - Multi-level paths get the path as display label
     *
     * Examples:
     *   (CSV)        -> [[:CSV]]
     *   (#CSV)       -> [[:CSV]]
     *   (CSV:TO-DO)  -> [[:CSV:TO-DO|CSV:TO-DO]]
     *   (CSV+TO-DO)  -> [[:CSV:TO-DO|CSV:TO-DO]]
     *   (CSV--TO-DO) -> [[:CSV:TO-DO|CSV:TO-DO]]
     *
     * @param string $text
     * @return string
     */
    protected function convertTagLinks(string $text): string
    {
        return preg_replace_callback(
            '/\(#?([\w][\w.:+\-]*)\)/',
            function ($m) {
                $path = preg_replace('/-{2,}/', ':', $m[1]);
                $path = str_replace('+', ':', $path);
                $alias = (strpos($path, ':') !== false) ? '|' . $path : '';
                return '([[:' . $path . $alias . ']])';
            },
            $text
        );
    }

    /**
     * Parse and render a string as DokuWiki inline syntax.
     * This allows internal links ([[page]]), external URLs, bold, italic, etc.
     * Obsidian-style and TAG-style links are converted to DokuWiki format first.
     *
     * @param Doku_Renderer $renderer
     * @param string $text
     */
    protected function renderInline(Doku_Renderer $renderer, string $text)
    {
        $text = $this->convertObsidianLinks($text);
        $text = $this->convertDateLinks($text);
        $text = $this->convertTagLinks($text);
        $instructions = p_get_instructions($text);
        $skip = ['document_start', 'document_end', 'p_open', 'p_close'];
        foreach ($instructions as $instruction) {
            if (in_array($instruction[0], $skip, true)) continue;
            call_user_func_array([$renderer, $instruction[0]], $instruction[1] ?: []);
        }
    }
}
