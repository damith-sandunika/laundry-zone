<script>
// The save-customer-btn listener is mostly handled by Alpine now, but we keep this just in case or we can rely fully on alpine
        // Wait, the new customer modal Alpine scope already handles it. So we can remove the vanilla JS listener to prevent duplication.

        function printPopup(url) {
            // Open in a new popup window to avoid browser iframe printing restrictions
            // and to allow the user to see the chit/receipt control panel.
            window.open(url, 'PrintWindow', 'width=800,height=800,top=100,left=100');
        }

        function printOrderChit(orderId) {
            printPopup('/orders/' + orderId + '/chit?auto_print=1');
        }

        function printReceipt(orderId) {
            printPopup('/orders/' + orderId + '/print?auto_print=1');
        }
</script>
