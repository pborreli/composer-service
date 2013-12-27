

$(document).ready(function() {
    var dragDrop = $('p.drag-and-drop');
    var button = $('#progress a.btn-primary');
    var downloadLink = $('#download-link');
    $('input.file-chooser')
        .width(dragDrop.outerWidth())
        .height(dragDrop.outerHeight());

    $('textarea').on('blur', function() {
        dragDrop.removeClass('focused');
    }).on('focus', function() {
        dragDrop.addClass('focused');
    }).on('input propertychange', function() {
        button.removeClass('btn-success');
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

    channel.bind('consumer:error', function(data) {
        ladda.stop();
        $('#error').html(data.message).addClass('alert in');
    });

    channel.bind('consumer:success', function(data) {
        step('Done !', false, true);
        ladda.stop();

        downloadLink.addClass('in');
        downloadLink.attr('href', data.link);
    });

    channel.bind('consumer:new-step', function(data) {
        step(data.message, false);
    });

    channel.bind('consumer:step-error', function(data) {
        step(data.message, true);
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

    function step (message, error, last) {
        error = typeof error !== 'undefined' ? error : false;
        last = typeof last !== 'undefined' ? last : false;

        var lastChild = $('#steps li:last-child');
        lastChild.removeClass('text-muted').addClass(error ? 'danger' : 'success');

        if (error) {
            lastChild.html('<i class="glyphicon glyphicon-remove"></i> '+message);
        }else{
            lastChild.find('i').removeClass('glyphicon-time').addClass('glyphicon-ok');
            if (last) {
                $('<li class="success"><i class="glyphicon glyphicon-ok"></i> '+message+'</li>').appendTo('#steps');
            } else {
                $('<li class="text-muted"><i class="glyphicon glyphicon-time"></i> '+message+'</li>').appendTo('#steps');
            }
        }
    }

    // Setup the dnd listeners.
    var dropZone = document.getElementById('dnd-zone');
    dropZone.addEventListener('dragover', handleDragOver, false);
    dropZone.addEventListener("dragleave", handleDragLeave, false);
    dropZone.addEventListener('drop', handleFileSelect, false);

    $('form').on('submit', function() {
        ladda.start();
        $('#steps').addClass('fade').html(null).removeClass('fade');
        step('Validating composer.json', false);
        downloadLink.removeClass('in');
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function(json) {
                if(json.status == 'ok') {
                    step('Sending to queue', false);
                } else {
                    ladda.stop();
                    $('#error').html(json.message).addClass('alert in');
                }
            }
        });

        return false;
    });
});
