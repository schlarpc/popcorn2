<?php

require_once "inc/common.inc.php";
require_once "inc/config.inc.php";
require_once "inc/stream.inc.php";
require_once "inc/shoebox.inc.php";

$videos = array();
foreach ($config['video_dirs'] as $video_dir) {
    $videos = array_merge($videos, find_video_files($video_dir));
}

$sb = new Shoebox();
$sb_data = $sb->getMovieData(3276, true);
$sb_stream = $sb_data['langs'][0]['stream'];

?><!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Popcorn 2 API</title>
    </head>
    <body>
        <h1>Popcorn 2 API</h1>
        <h2>User Methods</h2>
        <p>
            <code><a href="/api/status">/api/status</a></code> - current playback status
        </p>
        <p>
            <code><a href="/api/thumbnail?path=<?php echo htmlspecialchars(urlencode($videos[0])); ?>&amp;time=120">/api/thumbnail</a></code> - get thumbnail at specified time
            <ul>
                <li><code>path</code> - video file path</li>
                <li><code>time</code> - time offset in seconds</li>
            </ul>
        </p>        
        <h2>Admin Methods</h2>
        <h3>General</h3>
        <p>
            <code><a href="/api/admin/videos">/api/admin/videos</a></code> - list videos on local filesystem
        </p>
        <p>
            <code><a href="/api/admin/play?path=<?php echo htmlspecialchars(urlencode($videos[0])); ?>&amp;time=0">/api/admin/play</a></code> - start video playback
            <ul>
                <li><code>path</code> - video file path</li>
                <li><code>time</code> - time offset in seconds (optional)</li>
            </ul>
        </p>
        <p>
            <code><a href="/api/admin/stop">/api/admin/stop</a></code> - stops video playback
        </p>
        <p>
            <code><a href="/api/admin/pause">/api/admin/pause</a></code> - pauses video playback
        </p>
        <p>
            <code><a href="/api/admin/resume">/api/admin/resume</a></code> - resumes video playback
        </p>
        <h3>Shoebox (streaming media library)</h3>
        <p>
            <code><a href="/api/admin/shoebox/search?q=pok">/api/admin/shoebox/search</a></code> - search shoebox content
            <ul>
                <li><code>q</code> - search query</li>
            </ul>
        </p>
        <p>
            <code><a href="/api/admin/shoebox/movie?mid=2978">/api/admin/shoebox/movie</a></code> - get information about a movie
            <ul>
                <li><code>mid</code> - movie id</li>
            </ul>
        </p>
        <p>
            <code><a href="/api/admin/shoebox/tv?sid=154">/api/admin/shoebox/tv</a></code> - get information about a tv series
            <ul>
                <li><code>sid</code> - show id</li>
            </ul>
        </p>
        <p>
            <code><a href="/api/admin/shoebox/episode?sid=154&amp;season=1&amp;episode=1">/api/admin/shoebox/episode</a></code> - get information about a tv episode
            <ul>
                <li><code>sid</code> - show id</li>
                <li><code>season</code> - season number</li>
                <li><code>episode</code> - episode number</li>
            </ul>
        </p>
        <h3>Downloads</h3>
        <p>
            <code><a href="/api/admin/download/youtube?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3DRZ5GTnE9y5E">/api/admin/download/youtube</a></code> - use youtube-dl to download a video
            <ul>
                <li><code>url</code> - video url</li>
            </ul>
        </p>
        <h2>Test URLs</h2>
        <ul>
            <li><a href="/api/admin/play?path=<?php echo htmlspecialchars(urlencode($sb_stream)); ?>">Start playback of a Shoebox movie</a></li>
        </ul>
    </body>
</html>
