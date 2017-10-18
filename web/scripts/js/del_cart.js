var cart = {
    products: [],
    actions: []
};
var price = +0;
$('#plus').on('click', plus);
$('#minus').on('click', minus);
$('#del').on('click', del);



function plus() {

}

function minus() {

}
function del() {

}

function showMiniCart() {
    var out="";
    for (var key in cart) {
        out = 'В корзине: '+ price.toFixed(2) +'руб.';
    }
    $('.nav-link.disabled').html(out);
}



function postCart(url)
{
    $.ajax({
        type: "POST",
        url: url,
        data: ({
            cart: cart
        })
    });
}