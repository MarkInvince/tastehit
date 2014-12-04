$(document).ready(function() {
    $.ajax({
        type: 'GET',
        url: 'https://www.tastehit.com/api/1001Maquettes/21ffe725-910d-4f99-9125-0f9f2adb18d1/r',
        dataType: 'script',
        cache: true}).done(function (response) {
        //alert('ok');
        console.log(response);
    });
});


