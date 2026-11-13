<?php

return [

    // The images of the brand that can be uploaded on the Pengaturan screen: the extensions and the biggest size accepted, and what is
    // shown while nothing was uploaded. SVG is left out on purpose, since it can run script.
    'images' => [
        'logo' => ['mimes' => ['png', 'jpg', 'jpeg', 'webp'], 'max_kb' => 512, 'bundled' => '/logo.png'],
        'favicon' => ['mimes' => ['png', 'ico'], 'max_kb' => 128, 'bundled' => '/favicon.ico'],
    ],

    // The content type an uploaded image is served as, by its extension; the type never comes from the file itself.
    'image_types' => ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'ico' => 'image/x-icon'],

    // The folder of the active storage that the images are kept in.
    'images_folder' => 'branding',

];
