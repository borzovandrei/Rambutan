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

    cart.products.push({
        id: id,
        prace: priceprod
    });
    console.log(cart);
    postCart(url);
    showMiniCart();
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