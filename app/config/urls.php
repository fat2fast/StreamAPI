<?php

return [
    'streamer' => [
        'POST streamer/start_room' => 'streamer/start-room',
        'POST streamer/close_room' => 'streamer/close-room',
    ],
    'audience' => [
        'GET audience/livestreams' => 'audience/livestreams',
        'GET audience/livestreams/<livestream_id:\\d+>' => 'audience/livestream',
    ],
];
