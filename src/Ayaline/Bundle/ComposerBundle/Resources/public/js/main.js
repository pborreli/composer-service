$(document).ready(function() {
    var dragDrop = $('p.drag-and-drop');
    var button = $('a.btn');
    $('input.file-chooser')
        .width(dragDrop.outerWidth())
        .height(dragDrop.outerHeight());

    $('textarea').on('blur', function() {
        dragDrop.removeClass('focused');
    }).on('focus', function() {
        dragDrop.addClass('focused');
    }).on('input propertychange', function() {
        button.removeClass('btn-success');
        button.find('.ladda-label').html('Validate');
        button.removeAttr('href');
        button.click(function(e){
            $('form').submit();
        });
    });

    var ladda = Ladda.create( document.querySelector( '.btn' ) );

    var pusher = new Pusher(pusher_key, { authEndpoint: channel_auth_endpoint });
    var sessionId = $.cookie('PHPSESSID');
    var channel = pusher.subscribe('private-channel-'+sessionId);

    button.click(function(e){
        $('form').submit();
    });

    channel.bind('notice', function(data) {
        //console.log(data);
    });

    channel.bind('success', function(data) {
        ladda.stop();
        button.find('.ladda-label').html('Download');
        button.addClass('btn-success');
        button.attr('href', data.link);
        button.unbind('click');
    });

    channel.bind('pusher:subscription_error', function(status) {
        if(status == 408 || status == 503){

        }
    })

    channel.bind('pusher:subscription_succeeded', function() {
        button.removeAttr('disabled');
    });

    $('form').on('submit', function() {
        ladda.start();
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function(json) {
                if(json.status == 'ok') {
                    button.find('.ladda-label').html('Processing...');
                } else {
                    console.log('Erreur : '+ json.status);
                }
            }
        });

        return false;
    });
});
