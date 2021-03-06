var video = document.getElementsByTagName("video")[0];
var onTime = true;
var videoList;
var current = {hours : 0, minutes : 0, seconds : 0, allseconds : 0};
var duration = {hours : 0, minutes : 0, seconds : 0, allseconds : 0};

function showProgressBar() {
    $('.progress').height(10);
    $('.progress').css('margin-top', '-6px');
    $('.slider').toggle();
}

function hideProgressBar() {
    $('.progress').height(5);
    $('.progress').css('margin-top', '0');
    $('.slider').toggle();
}

function seekProgressBar(e) {
    $.getJSON("/api/status", function (data) {
        var seconds = Math.floor((e.clientX / $(window).width()) * data.duration);
        $.getJSON("/api/admin/play", {"path": data.current_video, "time": seconds});
    });
}

function onVideoPlay() {
    $('#playpause img').attr('src', '/img/pause.png');
    syncElapsed();
    updateElapsed();
}

function onVideoPause() {
    $('#playpause img').attr('src', '/img/play.png');
    onTime = false;
    $('.time').css('color', '#CD181F');
    $('.time').attr('title', 'Desynchronized. Press to resync.');
}

function updateVolume() {
    $('video')[0].volume = $('#volume-bar').val();
}

function heartbeatStatus() {
    $.getJSON("/api/status", function (data) {
        if (data.streaming == true && data.time_elapsed != false) {
            reloadStream();
        } else {
            setTimeout(heartbeatStatus, 5000);
        }
    }, function () {
        setTimeout(heartbeatStatus, 5000);
    });
}

function toggleFullScreen() {
    if (!document.mozFullScreen && document.webkitCurrentFullScreenElement == null) {
        if (document.body.mozRequestFullScreen) {
            document.body.mozRequestFullScreen();
        } else {
            document.body.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } else {
        if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else {
            document.webkitCancelFullScreen();
        }
    }
}

function reloadStream() {
    video.load();
    onTime = true;
    syncElapsed();
    $('.time').css('color', '#777777');
    $('.time').attr('title', '');
}

function secondsToTime(secs) {
    return {
        "hours": Math.floor(secs / 3600),
        "minutes": Math.floor((secs % 3600) / 60),
        "seconds": secs % 60,
        "allseconds": secs,
    };
}

function timeToString(time) {
    var output = "";
    if (time.hours != 0) {
        output += padDigits(time.hours, 2) + ":";
    }
    output += padDigits(time.minutes, 2) + ":" + padDigits(time.seconds, 2);
    return output;
}

function syncElapsed() {
    if (onTime == true) {
        $.getJSON("/api/status", function (data) {
            current = secondsToTime(data.time_elapsed);
            duration = secondsToTime(data.duration);
        });
    }
    if (!video.paused) {
        setTimeout(syncElapsed, 15000);
    }
}

function togglePauseStream() {
    if (video.paused == true) {
        video.play();
    } else {
        video.pause();
    }
}

function padDigits(number, digits) {
    return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
}

function updateElapsed() {
    if (!video.paused) {
        current.seconds++;
        current.allseconds++;
        if (current.seconds >= 60) {
            current.minutes++;
            current.seconds = 0;
            if (current.minutes >= 60) {
                current.hours++;
                current.minutes = 0;
            }
        }
        var output = timeToString(current) + " / " + timeToString(duration);
        $('.time').html(output);
        setTimeout(updateElapsed, 1000);
    }
    $('.progressdone').css('width', '' + (current.allseconds / duration.allseconds * 100) + "%");

    if (video.ended == true) {
        console.log("Reached end of stream, scheduling heartbeat");
        heartbeatStatus();
    }
}

function stopVideo() {
    $.getJSON('/api/admin/stop');
}

function playMovie(filename) {
    $.getJSON('/api/admin/play', {'path': filename});
}

function togglePauseVideo() {
    if ($('#play-pause-video').data('state') == 'playing') {
        $.getJSON('/api/admin/pause')
        $('#play-pause-video').data('state', 'paused');
        $('#play-pause-video').html('Play All');
    } else {
        $.getJSON('/api/admin/resume');
        $('#play-pause-video').data('state', 'playing');
        $('#play-pause-video').html('Pause All');
    }
}

function toggleVideos() {
    if ($('.expandControl').css('bottom') == '0px') {
        $('.expandControl').css('bottom', '-474px');
    } else {
        $('.expandControl').css('bottom', 0);
        if (videoList == undefined) {
            $.getJSON("/api/admin/videos", function (data) {
                videoList = data;
                $.each(videoList, function (index, value) {
                    var prettyName = /^.*\/(.+)\.\w+$/.exec(value)[1].substring(0, 40);
                    $('.expandControl ul').append(
                        $('<li />').data('filename', value).append(
                            $('<article />').append($('<h2 />').text(prettyName))
                        ).attr('title', value)
                    );
                });
                $('.expandControl li').click(function () {
                    $('.expandControl li').removeClass('selectedItem');
                    if ($(this).has('.movieDetails').length == 0) {
                        var filename = $(this).data('filename');
                        $(this).append($('<div />').addClass('movieDetails').append(
                            $('<img />').attr('src', '/api/thumbnail?time=120&path=' + encodeURIComponent(filename)),
                            $('<div />').append(
                                $('<a />').text('Play').click(function () {
                                    console.log('Issuing play request for:', filename);
                                    playMovie(filename);
                                }),
                                $('<a />').text('Delete')
                            )
                        ));
                    }
                    $(this).addClass('selectedItem');
                });
            });
        }
    }
}

$(document).ready(function () {
    $('.progress').hover(showProgressBar, hideProgressBar).click(seekProgressBar);
    $('video').click(togglePauseStream).on('play', onVideoPlay).on('pause', onVideoPause);
    $('#volume-bar').change(updateVolume);
    $('#playpause').click(togglePauseStream);
    $('#refresh').click(reloadStream);
    $('#fullscreen').click(toggleFullScreen);
    $('.expandControl h1').click(toggleVideos);
    $('#play-pause-video').click(togglePauseVideo);
    //$('#add-video').click(addVideo);
    $('#stop-video').click(stopVideo);

    if ($('video')[0].networkState == 3) {
        console.log("No movie playing, scheduling heartbeat");
        heartbeatStatus();
    }
});
