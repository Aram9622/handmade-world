{
    const description = document.querySelector('[data-description-editor]');
    if (description) {
        const editor = description.querySelector('[data-rich-editor]');
        const fallback = description.querySelector('textarea');
        const html = description.querySelector('[name="description_html"]');
        const controls = description.querySelector('[data-rich-controls]');
        const toolbar = description.querySelector('.listing-editor-toolbar');
        let selection = null;

        document.addEventListener('selectionchange', () => {
            const current = window.getSelection();
            if (current.rangeCount && editor.contains(current.anchorNode) && editor.contains(current.focusNode)) {
                selection = current.getRangeAt(0).cloneRange();
            }
        });

        toolbar.addEventListener('click', event => {
            const button = event.target.closest('[data-command]');
            if (!button) return;
            editor.focus();
            if (selection && editor.contains(selection.commonAncestorContainer)) {
                const current = window.getSelection();
                current.removeAllRanges();
                current.addRange(selection);
            }
            document.execCommand(button.dataset.command, false, button.dataset.value ?? null);
        });

        editor.addEventListener('paste', event => {
            event.preventDefault();
            document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
        });
        editor.addEventListener('drop', event => event.preventDefault());

        controls.hidden = false;
        fallback.hidden = true;
        fallback.required = false;
        html.disabled = false;
        fallback.form.addEventListener('submit', () => {
            fallback.value = editor.innerText;
            html.value = editor.innerHTML;
        });
    }

    const characteristics = document.querySelector('[data-characteristics]');
    if (characteristics) {
        const rows = characteristics.querySelector('[data-characteristic-rows]');
        const template = characteristics.querySelector('[data-characteristic-template]');
        const add = characteristics.querySelector('[data-add-characteristic]');
        const limit = Number(characteristics.dataset.limit);
        let next = rows.children.length;

        function addRow() {
            const row = template.content.firstElementChild.cloneNode(true);
            for (const input of row.querySelectorAll('input')) {
                input.name = 'characteristics[' + next + '][' + input.dataset.field + ']';
            }
            next += 1;
            rows.append(row);
            add.disabled = rows.children.length >= limit;
            row.querySelector('input').focus();
        }

        add.hidden = false;
        add.disabled = rows.children.length >= limit;
        add.addEventListener('click', addRow);
        for (const button of rows.querySelectorAll('[data-remove-characteristic]')) {
            button.hidden = false;
        }
        rows.addEventListener('click', event => {
            const button = event.target.closest('[data-remove-characteristic]');
            if (!button) return;
            button.closest('[data-characteristic-row]').remove();
            add.disabled = false;
            if (!rows.children.length) {
                addRow();
            } else {
                add.focus();
            }
        });
    }
}
