var cart = {
    products: [],
    actions: []
};
var price = +0;
$('.btn-plus').click(plus);
$('.btn-minus').click(minus);
$('.btn-del').click(del);



function plus() {
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

function minus() {
    var id = $(this).attr('data-id');
    var priceprod = $(this).attr('data-price');
    var url = $(this).data('url');
    price += -priceprod;

    cart.products.shift();
    cart.products.push({
        id: id,
        prace: priceprod
    });

    delProductById(url,id);
    saveCart();
    showMiniCart();
}

function del() {
    var id = $(this).attr('data-id');
    var sum = $(this).attr('data-sum');
    var priceprod = $(this).attr('data-price'); //реализовать изменение цен
    var url = $(this).data('url');
    price -= priceprod * sum;

    cart.products.shift();
    cart.products.push({
        id: id,
        prace: priceprod
    });

    delProductById(url,id);
    saveCart();
    showMiniCart();
}

