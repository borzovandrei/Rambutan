$('.like').click(like);
$('.dislike').click(dislike);



function like() {
    var id = $(this).attr('data-id');
    var author = $(this).attr('data-author');
    var likes = +1;
    var url = $(this).attr('data-url');

    postLike(url, id, author, likes);
}



function dislike() {
    var id = $(this).attr('data-id');
    var author = $(this).attr('data-author');
    var likes = +0;
    var url = $(this).attr('data-url');

    postLike(url, id, author, likes);
}


function postLike(url, id, author, likes) {

    $.ajax({
        type: "POST",
        url: url,
        data: ({
            id: id,
            author:author,
            likes: likes
        })
    });

}
