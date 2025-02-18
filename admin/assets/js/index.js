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

function removeFromCart(productId) {
  fetch('<?php echo $_SERVER["SCRIPT_NAME"]; ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ action: 'remove_from_cart', productId: productId })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Product removed from cart! Total items: ' + data.cartCount);
      updateCartCount(data.cartCount);
      document.querySelector(`[data-product-id="${productId}"] .remove-from-cart`).style.display = 'none';
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

 
  document.querySelectorAll('.remove-from-cart').forEach(button => {
    button.addEventListener('click', () => {
      const productId = button.closest('.product-card').dataset.productId;
      removeFromCart(productId);
    });
  });
});


document.addEventListener('DOMContentLoaded', () => {
    const accountLink = document.querySelector('.account-link');
    const dropdownContent = document.querySelector('.dropdown-content');

    accountLink.addEventListener('click', (event) => {
        event.preventDefault(); 
        dropdownContent.classList.toggle('show');
    });

    
    window.addEventListener('click', (event) => {
        if (!event.target.matches('.account-link')) {
            if (dropdownContent.classList.contains('show')) {
                dropdownContent.classList.remove('show');
            }
        }
    });
});