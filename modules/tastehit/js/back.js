$(document).ready(function(){
    $('#cms_page_select').parent().parent().css('display', 'none');
    $('#MAIN_TERMS_AND_COND_on').on('change', function(){
        $('#cms_page_select').parent().parent().fadeIn("slow");
    });
    $('#MAIN_TERMS_AND_COND_off').on('change', function(){
        $('#cms_page_select').parent().parent().fadeOut("slow");
    });

	$('body').on('click','.view_quote',  function(){
		$(this).closest('form').submit();
	});

	$('body').on('click', '.delete_quote', function(){
		if(confirm(confirmDelete)) {
			$.ajax({
				method   : 'post',
				data     : 'action=delete&item='+ $(this).attr('rel'),
				url      : adminQuotesUrl,
				dataType :'json',
				success: function(response) {
					if(response.data.hasError == false) {
						$('#quotes_panel').empty();
						$('#quotes_panel').html(response.data.quotes);
					}
					else {
						alert(response.data.message);
					}
				}
			});
		}
	});

    //Delete bargain offer
    $('.deleteBargainOffer').on('click', function() {

        if(confirm(confirmDelete)){
            var $action = $(this).data('action');
            var $id_bargain = $(this).data('id');
            var $thisBargain = $(this).closest('.admin_bargain');

            $.ajax({
                url: adminQuotesUrl,
                method:'post',
                data:
                {
                    actionBargainDelete : $action,
                    id_bargain : $id_bargain
                },
                dataType:'json',
                success: function(data) {
                    console.log(data);
                    console.log(data.deleted);
                    if(data.hasError)
                        $('#danger_bargain_' + $id_bargain).css('display', 'block');
                    if(data.deleted){
                        $thisBargain.html(data.message);
                    }
                }
            });
        }
        return false;
    });

});