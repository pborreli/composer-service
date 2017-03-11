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
        button.click(handleButtonClick);
    });

    $('#composer_hasDevDependencies').change(function () {
        $('#label-hasDevDependencies').html($(this).is(':checked') ? '<i class="glyphicon glyphicon-check"></i> --dev' : '<i class="glyphicon glyphicon-unchecked"></i> --no-dev');
    });

    var ladda = Ladda.create(document.querySelector('.btn'));

    ZeroClipboard.setDefaults({ moviePath: '/bundles/ayalinecomposer/swf/ZeroClipboard.swf' });
    var clip = new ZeroClipboard();

    clip.on("dataRequested", function (client, args) {
        var text = '';
        $('#' + $(this).attr('data-target') + ' p').each(function() {
            text += $(this).text() + '\n';
        });

        client.setText($.trim(text));
    });

    var pusher = new Pusher(pusher_key, { authEndpoint: channel_auth_endpoint, disableStats: true });

    pusher.connection.bind('connected', function() {
        socketId = pusher.connection.socket_id;
        var channel = pusher.subscribe('private-channel-'+socketId);

        channel.bind('consumer:error', function(data) {
            ladda.stop();
            $('#error').html(data.message).addClass('alert in');
        });

        channel.bind('consumer:success', function(data) {
            end = new Date().getTime();
            step('Done in '+ (end - start)/1000 +' seconds!', false, true);
            ladda.stop();

            downloadLink.removeClass('hide');
            downloadLink.attr('href', data.link);
        });

        channel.bind('consumer:new-step', function(data) {
            step(data.message, false);
        });

        channel.bind('consumer:step-error', function(data) {
            step(data.message, true, false, data.alerts ? data.alerts : false);
        });

        channel.bind('consumer:composer-output', function(data) {
            addLogs('composer-output', data);
        });

        channel.bind('consumer:composer-installed', function(data) {
            addLogs('composer-installed', data);
        });

        channel.bind('consumer:vulnerabilities', function(data) {
            addVuln('vulnerabilities', data);
        });

        channel.bind('pusher:subscription_error', function(status) {
            if(status == 408 || status == 503){

            }
        });

        channel.bind('pusher:subscription_succeeded', function() {
            button.removeClass('disabled');
        });
    });

    var start = 0;
    var end = 0;

    function handleButtonClick() {
        $('.nav-tabs a:first').tab('show');
        $('.nav-tabs li:not(:first)').addClass('hide');
        $('.tab-content button[data-target]').each(function() {
            clip.unglue(this);
        });
        $('form').submit();
    }

    button.click(handleButtonClick);



    $('#file').change(function() {
        readFile(this.files[0])
    });

    function addLogs(id, data) {
        var content = '<p>' + data.message.split(/\r?\n/).join('</p><p>') + '</p>';
        $('#log-' + id).html(content);
        $('.nav-tabs a[href=#' + id + ']').parent().removeClass('hide');
    }

    function addVuln(id, data) {
        var vulns = jQuery.parseJSON(data.message);

        var content = '<p>';
        $.each(vulns, function(name, vuln) {

            content +=  '<ul><li><h4>'+ name + ' ('+ vuln.version + ') </h4>';
            console.log(name);
            $.each(vuln.advisories, function(yaml, advisory) {
                content +=  '<ul><li><a href="'+advisory.link+'">' + advisory.title + ' ' + advisory.cve +'</a></li></ul>';
            });
            content +=  '</li></ul>';
        });

        content +=  '</p>';
        $('#log-' + id).html(content);
        $('.nav-tabs a[href=#' + id + ']').parent().removeClass('hide');
    }

    function readFile(file) {
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
        } else {
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
        downloadLink.addClass('hide');
        ladda.start();
        $('#steps').addClass('fade').html(null).removeClass('fade');
        step('Validating composer.json', false);
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
                button.removeClass('active');
            }
        });

        return false;
    });

    $('.nav-tabs').delegate('a[data-toggle="tab"]', 'shown.bs.tab', function (e) {
        $relatedTab = $($(e.relatedTarget).attr('href'));
        var oldButton = $relatedTab.find('button[data-target]')[0];
        if(oldButton) {
            clip.unglue(oldButton);
        }

        $tab = $($(e.target).attr('href'));
        var newButton = $tab.find('button[data-target]')[0];
        if(newButton) {
            clip.glue(newButton);
        }
    });
});
