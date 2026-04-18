const products = [
    { id: 1, name: 'Снайперская винтовка', price: 900, img: 'vp.jpg', desc: 'Калиматорный прицел с ручным затвором.', link: 'VP.html' },
    { id: 2, name: 'Автомат Барез', price: 750, img: 'rifle.jpg', desc: 'Высокая скорострельность и огневая мощь.', link: 'Rifle.html' },
    { id: 3, name: 'Beretta M9', price: 500, img: 'M9.jpg', desc: 'Прост в использовании, легок в перезарядке.', link: 'M9.html' }
];

let cart = [];
const addToCart = (id) => {
    for (let i = 0; i < products.length; i++) {
        if (products[i].id === id) {
            let found = false;
            for (let j = 0; j < cart.length; j++) {
                if (cart[j].id === id) {
                    cart[j].count++;
                    found = true;
                    break;
                }
            }
            if (!found) {
                cart.push({ id: id, name: products[i].name, price: products[i].price, count: 1 });
            }
            break;
        }
    }
    showCart();
};

const removeFromCart = (id) => {
    let newCart = [];
    for (let i = 0; i < cart.length; i++) {
        if (cart[i].id !== id) {
            newCart.push(cart[i]);
        }
    }
    cart = newCart;
    showCart();
};

const clearCart = () => {
    cart = [];
    showCart();
};

const totalPrice = () => {
    let total = 0;
    for (let i = 0; i < cart.length; i++) {
        total += cart[i].price * cart[i].count;
    }
    return total;
};

const showProducts = () => {
    let filter = document.getElementById('filterInput').value.toLowerCase();
    let html = '';
    
    for (let i = 0; i < products.length; i++) {
        let name = products[i].name.toLowerCase();
        if (name.indexOf(filter) !== -1) {
            html = html + `
                <div class="product-card">
                    <img src="${products[i].img}">
                    <h3><a href="${products[i].link}">${products[i].name}</a></h3>
                    <p>${products[i].desc}</p>
                    <div class="price">${products[i].price}$</div>
                    <button onclick="addToCart(${products[i].id})">+ в корзину</button>
                </div>
            `;
        }
    }
    
    document.getElementById('productsGrid').innerHTML = html;
};

const showCart = () => {
    let html = '';
    
    if (cart.length === 0) {
        html = '<div class="empty-cart">Корзина пуста</div>';
    } else {
        for (let i = 0; i < cart.length; i++) {
            let item = cart[i];
            let sum = item.price * item.count;
            html = html + `
                <div class="cart-item">
                    <div>
                        <div>${item.name}</div>
                        <div>${item.price}$ × ${item.count} = ${sum}$</div>
                    </div>
                    <button onclick="removeFromCart(${item.id})">Remove</button>
                </div>
            `;
        }
    }
    
    document.getElementById('cartItems').innerHTML = html;
    
    if (cart.length > 0) {
        document.getElementById('cartTotal').innerHTML = 'ИТОГО: ' + totalPrice() + '$';
    } else {
        document.getElementById('cartTotal').innerHTML = '';
    }
};

const pay = () => {
    if (cart.length === 0) {
        alert('Корзина пуста!');
    } else {
        alert('Покупка прошла успешно!');
        cart = [];
        showCart();
    }
};

showProducts();
showCart();

document.getElementById('filterInput').oninput = showProducts;
document.getElementById('payBtn').onclick = pay;
document.getElementById('clearCartBtn').onclick = clearCart;

// adadada