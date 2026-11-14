<x-slot name="scripts">
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('emailTemplateManager', () => ({
            editorOpen: false,
            selectedTemplate: null,
            editSubject: '',
            editBody: '',

            openEditor(template) {
                this.selectedTemplate = template;
                this.editSubject = template.subject;
                this.editBody = template.body;
                this.editorOpen = true;
                this.$nextTick(() => {
                    this.updatePreview();
                });
            },

            updatePreview() {
                const frame = document.getElementById('emailPreviewFrame');
                if (!frame) return;
                let html = this.editBody
                    .replace(/\{customer_name\}/g, 'Damith Sandunika')
                    .replace(/\{order_id\}/g, '1042')
                    .replace(/\{total\}/g, 'Rs.1,250.00')
                    .replace(/\{amount_due\}/g, 'Rs.750.00')
                    .replace(/\{payment_status\}/g, 'Partially Paid');

                const doc = frame.contentDocument || frame.contentWindow.document;
                doc.open();
                doc.write(html);
                doc.close();
            },

            saveTemplate() {
                if (!this.selectedTemplate) return;
                const form = document.getElementById('saveTemplateForm');
                form.action = '/email-templates/' + this.selectedTemplate.id;
                document.getElementById('saveSubject').value = this.editSubject;
                document.getElementById('saveBody').value = this.editBody;
                document.getElementById('saveIsActive').value = this.selectedTemplate.is_active ? '1' : '0';
                form.submit();
            }
        }));
    });
</script>
</x-slot>
