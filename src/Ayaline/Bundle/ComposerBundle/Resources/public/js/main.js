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
        button.unbind('click');
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
        button.find('.ladda-label').html(data.message);
    });

    channel.bind('success', function(data) {
        ladda.stop();
        button.find('.ladda-label').html('<i class="glyphicon glyphicon-download"></i> Download');
        button.addClass('btn-success');
        button.attr('href', data.link);
        button.unbind('click');
    });

    channel.bind('error', function(data) {
        ladda.stop();
        button.find('.ladda-label').html('Validate');
        $('#error').html(data.message).addClass('alert in');
    });

    channel.bind('pusher:subscription_error', function(status) {
        if(status == 408 || status == 503){

        }
    })

    channel.bind('pusher:subscription_succeeded', function() {
        button.removeClass('disabled');
    });

    $('#file').change(function() {
        readFile(this.files[0])
    });

    function readFile(file) {
        console.log(file.type);

        if (file.type != 'application/json'){
            $('.bad-file').addClass('in');

            return false;
        }
        var reader = new FileReader();
        reader.onload = function(event) {
            var content = event.target.result;
            $('textarea').val(content);
        };
        reader.readAsText(file);
    }

    function handleFileSelect(event) {
        $(this).removeClass("dragover");
        event.stopPropagation();
        event.preventDefault();
        readFile(event.dataTransfer.files[0])
    }

    function handleDragOver(event) {
        $(this).addClass("dragover");
        event.stopPropagation();
        event.preventDefault();
        event.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
    }

    function handleDragLeave(event) {
        $(this).removeClass("dragover");
        event.stopPropagation();
        event.preventDefault();
        event.dataTransfer.dropEffect = 'none';
    }

    // Setup the dnd listeners.
    var dropZone = document.getElementById('dnd-zone');
    dropZone.addEventListener('dragover', handleDragOver, false);
    dropZone.addEventListener("dragleave", handleDragLeave, false);
    dropZone.addEventListener('drop', handleFileSelect, false);

    $('form').on('submit', function() {
        ladda.start();
        button.find('.ladda-label').html('Validating...');
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function(json) {
                if(json.status != 'ok') {
                    console.log('Erreur : '+ json.status);
                }
            }
        });

        return false;
    });
});
