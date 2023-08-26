$(document).ready(function () {

    $('body').on('change', '#email', function(){
        var $this = $(this);
        let url = $this.data('invites_link');
        $.ajax({
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'text',
            data: {
                'email': $this.val(),
            },
            success: function (data) {
                var $data = JSON.parse(data);
                if ($data.result) {
                    var $search_data = JSON.parse($data.data);
                    if(typeof $search_data.id != 'undefined' && $search_data.id > 0) {
                        $('#company').val($search_data.title);
                        $('#company_id').val($search_data.id);
                        $('#input_company_id').val($search_data.id);
                        $('.notify-company').show(300);
                    }
                } else {
                    alert('Error occured. Try again later.')
                }
            }
        });
    });

    $('body').on('change', '#company', function(){
        if(typeof $('#input_company_id').val() != 'undefined' && $('#input_company_id').val() > 0 && $('#input_company_id').val() != $('#company_id').val()){
            $('#input_company_id').val(0);
            $('#alertModal').modal('show');
            $('#alertModal, .modal-backdrop').addClass('show');
        }
    });

    $('#company').autocomplete({
        minLength: 2,
        source: function (request, response) {
            let url = $('#company').data('link');
            $.ajax({
                url: url,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'text',
                data: {
                    'term': request.term,
                    'limit': 10,
                },
                success: function (data) {
                    var $data = JSON.parse(data);
                    if ($data.result) {
                        var results = [];
                        var $search_data = JSON.parse($data.data);
                        if($search_data.length  == 0){
                            $('#company_id').val('');
                            $('.notify-company').hide(300);
                        }
                        $.each($search_data, function (key, value) {
                            results.push({
                                label: value.title,
                                value: key,
                                id:value.id,
                            });
                        });
                        response(results);
                    } else {
                        alert('Error occured. Try again later.')
                    }
                }
            });

        },
        focus: function(event, ui) {
            event.preventDefault();
        },
        select: function(event, ui) {
            event.preventDefault();
            $(event.target).val(ui.item.label);
            $('#company_id').val(ui.item.id);
            $('.notify-company').show(300);
        }

    });
});
