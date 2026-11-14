<script>
    window.finalizeBillApi = {
        async saveOrder(orderNumber, editReason, cart, discountType, discountPercent, discountAmount, subtotal, deliveryFee) {
            if (cart.length === 0) return alert("Cart is empty!");
            if (!editReason) return alert("Please provide a reason for the edit.");

            try {
                const res = await fetch(`/orders/${orderNumber}/corrections`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content
                    },
                    body: JSON.stringify({
                        type: 'order_change',
                        reason: editReason,
                        new_data: {
                            cart: cart,
                            discount_type: discountType,
                            discount_percentage: discountType === 'percent' ? discountPercent : (subtotal > 0 ? (discountAmount / subtotal) * 100 : 0),
                            discount_amount: discountAmount,
                            delivery_fee: deliveryFee
                        }
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = `/orders/view/${orderNumber}`;
                } else {
                    alert(data.message || "Failed to submit correction request");
                }
            } catch (e) {
                alert("Error submitting request");
            }
        }
    };
</script>
