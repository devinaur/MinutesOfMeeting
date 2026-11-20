<?php

return [
    'uploaded' => 'The :attribute failed to upload. The file size may exceed the maximum limit (30MB) or the format is not supported.',
    'max' => [
        'file' => 'The :attribute may not be greater than :max kilobytes.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'attributes' => [
        'audioFile' => 'audio recording file',
    ],
    'custom' => [
        'audioFile' => [
            'uploaded' => 'The audio recording failed to upload. The file size exceeds the allowed limit.',
            'max' => 'The audio recording may not be greater than 30MB.',
            'mimes' => 'The audio recording must be a file of type: mp3, m4a, wav, ogg, aac, txt.',
        ],
    ],
];
