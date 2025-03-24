
    function addToCart(productId) {
        fetch('<?php echo $_SERVER["SCRIPT_NAME"]; ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'add_to_cart', productId: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to cart! Total items: ' + data.cartCount);
                updateCartCount(data.cartCount);
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateCartCount(count) {
        document.querySelector('.header-icons span').innerText = 'Cart (' + count + ')';
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.closest('.product-card').dataset.productId;
                addToCart(productId);
            });
        });
    });

document.addEventListener("contextmenu", function (e) {
  e.preventDefault(); 
});

document.addEventListener("keydown", function (e) {
  if (e.ctrlKey && (e.key === "u" || e.key === "U")) {
    e.preventDefault(); 
  }
  if (e.key === "F12") {
    e.preventDefault();
  }
  if (e.ctrlKey && e.shiftKey && (e.key === "I" || e.key === "J")) {
    e.preventDefault(); 
  }
});
