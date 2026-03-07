/**
 * DokuWiki Plugin myshortcuts (JavaScript Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author David Jiménez <davidjimenez75@gmail.com>
 */

var MyShortcutsPlugin = {

    /**
     * Language strings
     */
    lang: {
        en: {
            selectSnippet: 'Select a Snippet',
            noSnippets: 'No snippets configured. Please add snippets in the plugin settings.',
            snippetsEditOnly: 'Snippets can only be inserted in edit mode. Press {0} to edit first.',
            cancel: 'Cancel (ESC)',
            editorNotFound: 'Editor not found.',
            saveNotInEdit: 'MyShortcuts: Save button not found. Are you in edit mode?',
            voiceStart: 'Start voice dictation',
            voiceStop: 'Stop voice dictation (click to stop)'
        },
        es: {
            selectSnippet: 'Seleccionar un Fragmento',
            noSnippets: 'No hay fragmentos configurados. Por favor añade fragmentos en la configuración del plugin.',
            snippetsEditOnly: 'Los fragmentos solo se pueden insertar en modo edición. Presiona {0} para editar primero.',
            cancel: 'Cancelar (ESC)',
            editorNotFound: 'Editor no encontrado.',
            saveNotInEdit: 'MyShortcuts: Botón de guardar no encontrado. ¿Estás en modo edición?',
            voiceStart: 'Iniciar dictado por voz',
            voiceStop: 'Detener dictado por voz (clic para detener)'
        }
    },

    /**
     * Get current language
     */
    getLang: function() {
        // Try to detect DokuWiki language
        if (typeof JSINFO !== 'undefined' && JSINFO.lang) {
            return JSINFO.lang;
        }
        // Fallback to browser language
        var browserLang = navigator.language || navigator.userLanguage;
        return browserLang.split('-')[0];
    },

    /**
     * Get translated string
     */
    getString: function(key) {
        var currentLang = MyShortcutsPlugin.getLang();
        var strings = MyShortcutsPlugin.lang[currentLang] || MyShortcutsPlugin.lang.en;
        return strings[key] || MyShortcutsPlugin.lang.en[key] || key;
    },

    /**
     * Initialize the plugin
     */
    init: function() {
        // Check if config is available
        if (typeof MYSHORTCUTS_CONFIG === 'undefined') {
            return;
        }

        // Add keyboard event listener
        document.addEventListener('keydown', function(e) {
            MyShortcutsPlugin.handleKeyPress(e);
        });

        // Initialize voice input
        MyShortcutsPlugin.VoiceInput.init();
    },

    /**
     * Parse keyboard shortcut string (e.g., "ctrl+e", "alt+s")
     */
    parseShortcut: function(shortcut) {
        if (!shortcut) return null;

        var parts = shortcut.toLowerCase().split('+');
        var result = {
            ctrl: false,
            alt: false,
            shift: false,
            meta: false,
            key: ''
        };

        parts.forEach(function(part) {
            part = part.trim();
            if (part === 'ctrl' || part === 'control') {
                result.ctrl = true;
            } else if (part === 'alt') {
                result.alt = true;
            } else if (part === 'shift') {
                result.shift = true;
            } else if (part === 'meta' || part === 'cmd') {
                result.meta = true;
            } else {
                result.key = part;
            }
        });

        return result;
    },

    /**
     * Check if pressed keys match a shortcut
     */
    matchesShortcut: function(event, shortcut) {
        var parsed = MyShortcutsPlugin.parseShortcut(shortcut);
        if (!parsed) return false;

        var key = event.key.toLowerCase();

        return (
            event.ctrlKey === parsed.ctrl &&
            event.altKey === parsed.alt &&
            event.shiftKey === parsed.shift &&
            event.metaKey === parsed.meta &&
            key === parsed.key
        );
    },

    /**
     * Handle keyboard shortcuts
     */
    handleKeyPress: function(e) {
        var config = MYSHORTCUTS_CONFIG;

        // Edit shortcut
        if (MyShortcutsPlugin.matchesShortcut(e, config.shortcutEdit)) {
            e.preventDefault();
            MyShortcutsPlugin.triggerEdit();
            return;
        }

        // Save shortcut
        if (MyShortcutsPlugin.matchesShortcut(e, config.shortcutSave)) {
            e.preventDefault();
            MyShortcutsPlugin.triggerSave();
            return;
        }

        // Snippet shortcut
        if (MyShortcutsPlugin.matchesShortcut(e, config.shortcutSnippet)) {
            e.preventDefault();
            MyShortcutsPlugin.showSnippetDialog();
            return;
        }
    },

    /**
     * Trigger edit action
     */
    triggerEdit: function() {
        // Look for edit button in DokuWiki
        var editBtn = document.querySelector('button[name="do"][value="edit"]') ||
                     document.querySelector('a.action.edit') ||
                     document.querySelector('a[href*="do=edit"]');

        if (editBtn) {
            editBtn.click();
        } else {
            // Construct edit URL manually
            var url = window.location.href;
            if (url.indexOf('?') > -1) {
                url += '&do=edit';
            } else {
                url += '?do=edit';
            }
            window.location.href = url;
        }
    },

    /**
     * Trigger save action
     */
    triggerSave: function() {
        // Only works in edit mode
        var saveBtn = document.querySelector('#edbtn__save') ||
                     document.getElementById('edbtn__save') ||
                     document.querySelector('button[name="do"][value="save"]');

        if (saveBtn) {
            saveBtn.click();
        } else {
            console.log(MyShortcutsPlugin.getString('saveNotInEdit'));
        }
    },

    /**
     * Show snippet selection dialog
     */
    showSnippetDialog: function() {
        var config = MYSHORTCUTS_CONFIG;

        if (!config.snippets || config.snippets.length === 0) {
            alert(MyShortcutsPlugin.getString('noSnippets'));
            return;
        }

        // Check if we're in edit mode
        var editor = document.getElementById('wiki__text');
        if (!editor) {
            var msg = MyShortcutsPlugin.getString('snippetsEditOnly').replace('{0}', config.shortcutEdit);
            alert(msg);
            return;
        }

        // Remove any existing dialogs first
        var existingOverlays = document.querySelectorAll('.myshortcuts-overlay');
        existingOverlays.forEach(function(overlay) {
            overlay.parentNode.removeChild(overlay);
        });

        // Create dialog
        var dialog = MyShortcutsPlugin.createSnippetDialog(config.snippets);
        document.body.appendChild(dialog);

        // Focus first item
        var firstItem = dialog.querySelector('.myshortcuts-snippet-item');
        if (firstItem) {
            firstItem.focus();
        }
    },

    /**
     * Create snippet selection dialog
     */
    createSnippetDialog: function(snippets) {
        var overlay = document.createElement('div');
        overlay.className = 'myshortcuts-overlay';

        // Store snippets map for keyboard access
        overlay._snippetsMap = {};

        var dialog = document.createElement('div');
        dialog.className = 'myshortcuts-dialog';

        var header = document.createElement('h3');
        header.textContent = MyShortcutsPlugin.getString('selectSnippet');
        dialog.appendChild(header);

        var list = document.createElement('div');
        list.className = 'myshortcuts-snippet-list';

        snippets.forEach(function(snippet, index) {
            var item = document.createElement('button');
            item.className = 'myshortcuts-snippet-item';
            item.setAttribute('tabindex', '0');
            item.setAttribute('data-index', index);
            item.setAttribute('data-number', snippet.number || snippet.label);

            // Store snippet in map for keyboard access
            overlay._snippetsMap[snippet.number || snippet.label] = snippet.text;

            var keyIndicator = document.createElement('span');
            keyIndicator.className = 'myshortcuts-key-indicator';
            keyIndicator.textContent = snippet.number || snippet.label;
            item.appendChild(keyIndicator);

            var content = document.createElement('div');
            content.className = 'myshortcuts-snippet-content';

            var text = document.createElement('div');
            text.className = 'myshortcuts-snippet-preview';
            text.textContent = snippet.text.substring(0, 80) + (snippet.text.length > 80 ? '...' : '');
            content.appendChild(text);

            item.appendChild(content);

            item.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                MyShortcutsPlugin.insertSnippet(snippet.text);
                MyShortcutsPlugin.closeDialog(overlay);
            });

            list.appendChild(item);
        });

        dialog.appendChild(list);

        var closeBtn = document.createElement('button');
        closeBtn.className = 'myshortcuts-close-btn';
        closeBtn.textContent = MyShortcutsPlugin.getString('cancel');
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            MyShortcutsPlugin.closeDialog(overlay);
        });
        dialog.appendChild(closeBtn);

        overlay.appendChild(dialog);

        // Handle keyboard shortcuts
        overlay.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                MyShortcutsPlugin.closeDialog(overlay);
                return;
            }

            // Handle number keys (1-9, 0)
            var key = e.key;
            if (/^[0-9]$/.test(key)) {
                e.preventDefault();
                var snippetText = overlay._snippetsMap[key];
                if (snippetText) {
                    MyShortcutsPlugin.insertSnippet(snippetText);
                    MyShortcutsPlugin.closeDialog(overlay);
                }
            }
        });

        // Close on overlay click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                MyShortcutsPlugin.closeDialog(overlay);
            }
        });

        return overlay;
    },

    /**
     * Close dialog
     */
    closeDialog: function(overlay) {
        // Remove all overlays as a safety measure
        var allOverlays = document.querySelectorAll('.myshortcuts-overlay');
        allOverlays.forEach(function(o) {
            if (o.parentNode) {
                o.parentNode.removeChild(o);
            }
        });
    },

    /**
     * Insert snippet into editor
     */
    insertSnippet: function(text) {
        var editor = document.getElementById('wiki__text');
        if (!editor) {
            alert(MyShortcutsPlugin.getString('editorNotFound'));
            return;
        }

        // Get cursor position
        var startPos = editor.selectionStart;
        var endPos = editor.selectionEnd;

        // Insert text at cursor position
        var beforeText = editor.value.substring(0, startPos);
        var afterText = editor.value.substring(endPos, editor.value.length);
        editor.value = beforeText + text + afterText;

        // Set cursor position after inserted text
        var newPos = startPos + text.length;
        editor.selectionStart = newPos;
        editor.selectionEnd = newPos;

        // Focus editor
        editor.focus();
    },

    /**
     * Voice input module using Web Speech API
     */
    VoiceInput: {
        recognition: null,
        isRecording: false,
        button: null,
        cursorStart: null,
        cursorEnd: null,

        isSupported: function() {
            return !!(window.SpeechRecognition || window.webkitSpeechRecognition);
        },

        init: function() {
            if (!MYSHORTCUTS_CONFIG.voiceEnabled) return;
            if (!MyShortcutsPlugin.VoiceInput.isSupported()) return;

            // Only active in edit mode
            var editor = document.getElementById('wiki__text');
            if (!editor) return;

            MyShortcutsPlugin.VoiceInput.injectButton();
        },

        injectButton: function() {
            var toolbar = document.getElementById('tool__bar');
            if (!toolbar) return;

            var btn = document.createElement('button');
            btn.id = 'myshortcuts-voice-btn';
            btn.className = 'myshortcuts-voice-btn';
            btn.type = 'button';
            btn.title = MyShortcutsPlugin.getString('voiceStart');
            btn.setAttribute('aria-label', MyShortcutsPlugin.getString('voiceStart'));
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm-1-9c0-.55.45-1 1-1s1 .45 1 1v6c0 .55-.45 1-1 1s-1-.45-1-1V5zm6 6c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>';

            // Save cursor position before button click changes focus
            btn.addEventListener('mousedown', function() {
                var editor = document.getElementById('wiki__text');
                if (editor) {
                    MyShortcutsPlugin.VoiceInput.cursorStart = editor.selectionStart;
                    MyShortcutsPlugin.VoiceInput.cursorEnd = editor.selectionEnd;
                }
            });

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                MyShortcutsPlugin.VoiceInput.toggle();
            });

            MyShortcutsPlugin.VoiceInput.button = btn;
            toolbar.appendChild(btn);
        },

        toggle: function() {
            if (MyShortcutsPlugin.VoiceInput.isRecording) {
                MyShortcutsPlugin.VoiceInput.stop();
            } else {
                MyShortcutsPlugin.VoiceInput.start();
            }
        },

        start: function() {
            var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            var recognition = new SpeechRecognition();

            recognition.lang = MyShortcutsPlugin.getLang();
            recognition.continuous = true;
            recognition.interimResults = false;

            recognition.onstart = function() {
                MyShortcutsPlugin.VoiceInput.isRecording = true;
                MyShortcutsPlugin.VoiceInput.setButtonState(true);
            };

            recognition.onresult = function(event) {
                var transcript = '';
                for (var i = event.resultIndex; i < event.results.length; i++) {
                    if (event.results[i].isFinal) {
                        transcript += event.results[i][0].transcript;
                    }
                }
                if (transcript) {
                    MyShortcutsPlugin.VoiceInput.insertText(transcript);
                }
            };

            recognition.onerror = function(event) {
                console.error('MyShortcuts voice error:', event.error);
                MyShortcutsPlugin.VoiceInput.isRecording = false;
                MyShortcutsPlugin.VoiceInput.setButtonState(false);
                MyShortcutsPlugin.VoiceInput.recognition = null;
            };

            recognition.onend = function() {
                MyShortcutsPlugin.VoiceInput.isRecording = false;
                MyShortcutsPlugin.VoiceInput.setButtonState(false);
                MyShortcutsPlugin.VoiceInput.recognition = null;
            };

            MyShortcutsPlugin.VoiceInput.recognition = recognition;
            recognition.start();
        },

        stop: function() {
            if (MyShortcutsPlugin.VoiceInput.recognition) {
                MyShortcutsPlugin.VoiceInput.recognition.stop();
            }
        },

        setButtonState: function(recording) {
            var btn = MyShortcutsPlugin.VoiceInput.button;
            if (!btn) return;
            if (recording) {
                btn.classList.add('myshortcuts-voice-recording');
                btn.title = MyShortcutsPlugin.getString('voiceStop');
                btn.setAttribute('aria-label', MyShortcutsPlugin.getString('voiceStop'));
            } else {
                btn.classList.remove('myshortcuts-voice-recording');
                btn.title = MyShortcutsPlugin.getString('voiceStart');
                btn.setAttribute('aria-label', MyShortcutsPlugin.getString('voiceStart'));
            }
        },

        insertText: function(text) {
            var editor = document.getElementById('wiki__text');
            if (!editor) return;

            // Restore cursor position saved before button click changed focus
            if (MyShortcutsPlugin.VoiceInput.cursorStart !== null) {
                editor.selectionStart = MyShortcutsPlugin.VoiceInput.cursorStart;
                editor.selectionEnd = MyShortcutsPlugin.VoiceInput.cursorEnd;
                MyShortcutsPlugin.VoiceInput.cursorStart = null;
                MyShortcutsPlugin.VoiceInput.cursorEnd = null;
            }

            MyShortcutsPlugin.insertSnippet(text + ' ');
        }
    }
};

// Initialize after DokuWiki's toolbar is ready.
// DokuWiki builds the toolbar via addInitEvent (window.onload chain),
// so we must run after that — not on DOMContentLoaded.
if (typeof addInitEvent === 'function') {
    // Loaded as part of DokuWiki's combined JS: use its init chain.
    addInitEvent(function() { MyShortcutsPlugin.init(); });
} else {
    // Loaded standalone (e.g. explicit <head> tag): defer to window.onload
    // so DokuWiki's toolbar init has already run.
    window.addEventListener('load', function() {
        MyShortcutsPlugin.init();
    });
}
