var cart = {
    products: [],
    actions: []
};

var price = +0;
$('.btn.btn-outline-success').on('click', addToCart);
$('.btn-default.btn').on('click', delCart);
$('#order_Оформить').on('click', delCart);


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
    localStorage.setItem('cost', JSON.stringify(price.toFixed(2)));
}

function loadCart() {
    if (localStorage.getItem('cost')) {
        if (localStorage.getItem('cost')==="NaN"){
            cost=0
        }
        cost = JSON.parse(localStorage.getItem('cost'));
        price = +cost;
        showMiniCart();
    }
}

function delCart() {
    if (localStorage.getItem('cost')) {
        localStorage.setItem('cost', 0);
        showMiniCart();
    }
}

function showMiniCart() {
    var out = "Корзина";
    if(JSON.parse(localStorage.getItem('cost')) === 0.00){
        out = 'Корзина';
    }else if(JSON.parse(localStorage.getItem('cost')) === "NaN"){
        out = 'Корзина';
    }else {
        out = 'В корзине: ' + price.toFixed(2) + 'руб.';
    }
    $('.nav-link.disabled').html(out);
    $('#sum_cart').html(price.toFixed(2)+ 'руб.');
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