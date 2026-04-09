/**
 * DokuWiki user script — loaded on all pages for all users.
 *
 * Ctrl+8  Insert 80 dashes + newline at cursor position.
 * Ctrl+I  Insert Obsidian frontmatter + heading at the top of the page.
 */
document.addEventListener('DOMContentLoaded', function () {
    var textarea = document.getElementById('wiki__text');
    if (!textarea) return;

    textarea.addEventListener('keydown', function (e) {
        if (!e.ctrlKey || e.altKey || e.shiftKey) return;

        // ── Ctrl+8 ─────────────────────────────────────────────────────────
        if (e.key === '8') {
            e.preventDefault();

            var insert = '-'.repeat(80) + '\n';
            var start  = this.selectionStart;
            var end    = this.selectionEnd;

            this.value = this.value.substring(0, start) + insert + this.value.substring(end);
            this.selectionStart = this.selectionEnd = start + insert.length;

            this.dispatchEvent(new Event('input', { bubbles: true }));
        }

        // ── Ctrl+I ─────────────────────────────────────────────────────────
        // Inserts Obsidian frontmatter (fields in alphabetical order) +
        // a H1 heading using the current page ID, at the top of the document.
        if (e.key === 'i' || e.key === 'I') {
            e.preventDefault();

            var pageId = (typeof JSINFO !== 'undefined' && JSINFO.id) ? JSINFO.id : '';
            var today  = new Date().toISOString().split('T')[0];

            var frontmatter = [
                '---',
                'aliases: ',
                'author: ',
                'created: ' + today,
                'description: ',
                'status: ',
                'tags:',
                '  - #tag1',
                'title: ',
                'updated: ' + today,
                '---',
                '# ' + pageId,
                '',
                ''
            ].join('\n');

            this.value = frontmatter + this.value;
            this.selectionStart = this.selectionEnd = frontmatter.length;

            this.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });
});
