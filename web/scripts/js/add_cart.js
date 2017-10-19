var cart = {
    products: [],
    actions: []
};

var price = +0;
$('.btn.btn-secondary').on('click', addToCart);


function addToCart() {
    var id = $(this).attr('data-id');
    var priceprod = $(this).attr('data-price');
    var url = $(this).data('url');
    price += +priceprod;


    cart.products.shift();
    cart.products.push({
        id: id,
        prace: priceprod
    });

    postCart(url);
    saveCart();
    showMiniCart();
}


function saveCart() {
    localStorage.setItem('cost', JSON.stringify(price));
}

function loadCart() {
    if (localStorage.getItem('cost')) {
        cost = JSON.parse(localStorage.getItem('cost'));
        price = +cost;
        showMiniCart();
    }
}


function showMiniCart() {
    var out = "Корзина";
    if(JSON.parse(localStorage.getItem('cost')) === 0){
        out = 'Корзина';
    }else {
        out = 'В корзине: ' + price.toFixed(2) + 'руб.';
    }
    $('.nav-link.disabled').html(out);
}


function postCart(url) {

    $.ajax({
        type: "POST",
        url: url,
        data: ({
            cart: cart
        })
    });

}

function delProductById(url, id) {

    $.ajax({
        type: "POST",
        url: url,
        data: {
            id: id
        }
    });
}


$(document).ready(function () {
    loadCart();
    showMiniCart();
});