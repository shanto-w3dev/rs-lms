<?php

require_once __DIR__ . '../../../../../wp-load.php';

$chapter_id = isset($_GET['chapter_id']) ? intval($_GET['chapter_id']) : 0;
$ep_number = isset($_GET['ep']) ? intval($_GET['ep']) : 0;
$autoplay = 0;

$video_src = '';
if($chapter_id > 0 && $ep_number > 0){
    $episodes = get_post_meta($chapter_id, 'rs_lms_chapter_episodes', true);
    $episodes_list = $episodes;
    $idx = $ep_number - 1;

    if($idx >= 0 && $idx < count($episodes_list)){
        $ep = $episodes_list[$idx];
        $url = isset($ep['video_url']) ? $ep['video_url'] : '';
        if ($url && preg_match('~^https?://~i', $url)) {
            $video_src = $url;
        }
    }
}

function rs_lms_guess_mime($url) {
    $path = parse_url($url, PHP_URL_PATH);
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'mp4': return 'video/mp4';
        case 'webm': return 'video/webm';
        case 'ogv': return 'video/ogg';
        case 'm3u8': return 'application/x-mpegURL';
        case 'mpd': return 'application/dash+xml';
        default: return '';
    }
}

if (!$video_src) {
    ?><!doctype html>
    <html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Video Not Found</title>
    <style>html,body{height:100%;margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif} .wrap{display:flex;align-items:center;justify-content:center;height:100%;background:#111;color:#eee} .box{padding:1rem 1.25rem;border-radius:8px;background:#1f2937} a{color:#93c5fd}</style>
    </head><body><div class="wrap"><div class="box">Video not found. Please check parameters.</div></div></body></html><?php
    exit;
}

$mime = rs_lms_guess_mime($video_src);

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Self-hosted Video</title>
    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
    <style>
        html, body { height: 100%; margin: 0; background: #000; }
        .player-wrap { height: 100%; width: 100%; display: flex; align-items: center; justify-content: center; }
        .video-js { width: 100% !important; height: 100% !important; }
    </style>
    <!-- Optional: include a basic CSS reset for iframe sizing -->
</head>
<body>
    <div class="player-wrap">
        <video id="rs-lms-selfhosted" class="video-js vjs-default-skin" controls playsinline <?php echo $autoplay ? 'autoplay muted' : ''; ?> preload="auto" data-setup='{}'>
            <source src="<?php echo esc_url($video_src); ?>" <?php echo $mime ? 'type="' . esc_attr($mime) . '"' : ''; ?> />
        </video>
    </div>
    <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
    <script>
        // Hook for future enhancements if needed
        const player = videojs('rs-lms-selfhosted');
    </script>
</body>
<?php /* Intentionally no wp_footer() to keep this iframe lean */ ?>
</html>