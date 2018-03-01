var cart = {
    products: [],
    actions: []
};

var price = +0;
$('.btn.btn-outline-success').on('click', addToCart);
$('.btn-default.btn').on('click', delCart);
$('#order_Оформить').on('click', delCart);

var cost = document.cookie.replace(/(?:(?:^|.*;\s*)cost\s*\=\s*([^;]*).*$)|^.*$/, "$1");

function addToCart() {
    var id = $(this).attr('data-id');
    var priceprod = $(this).attr('data-price');
    var url = $(this).data('url');

    if (priceprod === undefined) {
        return;
    }

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
    document.cookie = "cost=" + price.toFixed(2)+"; path=/";
}

function loadCart() {
    if (cost) {
        if (cost === "NaN") {
            cost = 0
        }

        price = +cost;
        showMiniCart();
    } else {
        price = 0;
        showMiniCart();
    }

}

function delCart() {
    if (cost) {
        document.cookie = "cost=0";
        showMiniCart();
    }
}

function showMiniCart() {
    var out = "Корзина";
    if (cost === 0.00) {
        out = 'Корзина';
    } else if (cost === "NaN") {
        out = 'Корзина';
    } else {
        out = 'В корзине: ' + price.toFixed(2) + 'руб.';
    }
    $('.nav-link.disabled').html(out);
    $('#sum_cart').html(price.toFixed(2) + 'руб.');
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
});