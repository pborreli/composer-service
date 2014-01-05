$(document).ready(function() {
    var dragDrop = $('p.drag-and-drop');
    var button = $('#progress button.btn-primary');
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

    $('#composer_hasDevDependencies').change(function () {
        $('#label-hasDevDependencies').html($(this).is(':checked') ? '<i class="glyphicon glyphicon-check"></i> --dev' : '<i class="glyphicon glyphicon-unchecked"></i> --no-dev');
    });

    var ladda = Ladda.create(document.querySelector('.btn'));

    var pusher = new Pusher(pusher_key, { authEndpoint: channel_auth_endpoint });
    var sessionId = $.cookie('COMPOSERAAS');
    var channel = pusher.subscribe('private-channel-'+sessionId);
    var start = 0;
    var end = 0;

    button.click(function(e) {
        $('form').submit();
    });

    channel.bind('consumer:error', function(data) {
        ladda.stop();
        $('#error').html(data.message).addClass('alert in');
    });

    channel.bind('consumer:success', function(data) {
        end = new Date().getTime();
        step('Done in '+ (end - start)/1000 +' seconds!', false, true);
        ladda.stop();

        downloadLink.addClass('in');
        downloadLink.attr('href', data.link);
    });

    channel.bind('consumer:new-step', function(data) {
        step(data.message, false);
    });

    channel.bind('consumer:step-error', function(data) {
        step(data.message, true, false, data.alerts ? data.alerts : false);
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

    function step(message, error, last, alerts) {
        error = typeof error !== 'undefined' ? error : false;
        last = typeof last !== 'undefined' ? last : false;
        alerts = typeof alerts !== 'undefined' ? alerts : false;

        var lastChild = $('#steps li:last-child');
        lastChild.removeClass('text-muted').addClass(error ? 'danger' : 'success');

        if (error) {
            if (false === alerts) {
                lastChild.html('<i class="glyphicon glyphicon-remove"></i> '+message);
            } else {
                lastChild.html('<i class="glyphicon glyphicon-remove"></i> <a data-placement="left" data-trigger="click" data-html="true" class="danger">'+message+'</a>');
                var link = lastChild.find('a');
                link.data('title', 'Security Report');
                link.data('content', alerts);
                link.data('container', 'body');
                link.popover();
            }
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
        start = new Date().getTime();
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
