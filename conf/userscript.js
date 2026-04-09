/**
 * DokuWiki user script — loaded on all pages for all users.
 *
 * Ctrl+8 in the editor: insert 80 dashes + newline at cursor position.
 */
document.addEventListener('DOMContentLoaded', function () {
    var textarea = document.getElementById('wiki__text');
    if (!textarea) return;

    textarea.addEventListener('keydown', function (e) {
        if (e.ctrlKey && !e.altKey && !e.shiftKey && e.key === '8') {
            e.preventDefault();

            var insert = '-'.repeat(80) + '\n';
            var start  = this.selectionStart;
            var end    = this.selectionEnd;

            this.value = this.value.substring(0, start) + insert + this.value.substring(end);
            this.selectionStart = this.selectionEnd = start + insert.length;

            // Notify DokuWiki of the change so the "unsaved changes" guard fires
            this.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });
});
