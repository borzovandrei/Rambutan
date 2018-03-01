var cart = {
    products: [],
    actions: []
};
var price = +0;
$('.btn-plus').click(plus);
$('.btn-minus').click(minus);
$('.btn-del').click(del);


function plus() {
    var num = $(this).attr('data-id');
    var sum = $('.sum' + num).attr('sum-id');
    document.getElementById('id' + sum).innerHTML++;

    var id = $(this).attr('data-id');
    var sum = $(this).attr('data-sum');

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
    var num = $(this).attr('data-id');
    var sum = $('.sum' + num).attr('sum-id');

    if (document.getElementById('id' + sum).innerHTML > 0) {
        document.getElementById('id' + sum).innerHTML--;
        var id = $(this).attr('data-id');
        var priceprod = $(this).attr('data-price');
        var url = $(this).data('url');
        price += -priceprod;
        cart.products.shift();
        cart.products.push({
            id: id,
            prace: priceprod
        });
        delProductById(url, id);
        saveCart();
        showMiniCart();
    }



}

function del() {
    var num = $(this).attr('data-id');
    var sum = $('.sum' + num).attr('sum-id');
    var prod = document.getElementById('id' + sum).innerHTML;
    document.getElementById('id' + sum).innerHTML = 0;

    var id = $(this).attr('data-id');
    var priceprod = $(this).attr('data-price'); //реализовать изменение цен
    var url = $(this).data('url');
    price -= priceprod * prod;

    cart.products.shift();
    cart.products.push({
        id: id,
        prace: priceprod
    });

    delProductById(url, id);
    saveCart();
    showMiniCart();
}

