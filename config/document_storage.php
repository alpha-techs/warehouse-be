<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Default document storage strategy
     |--------------------------------------------------------------------------
     |
     | Defines which logical storage type should be used when generating
     | downloadable documents (reports, invoice prints, etc.). Supported values:
     | "local" and "s3". The value is mapped to an actual filesystem disk via
     | the disk_map configuration below.
     */
    'default' => env('DOCUMENT_STORAGE', 'local'),

    /*
     |--------------------------------------------------------------------------
     | Storage type to filesystem disk mapping
     |--------------------------------------------------------------------------
     |
     | Allows mapping logical storage types to specific filesystem disks. This
     | makes it easy to point document generation to a different disk (e.g. a
     | dedicated S3 bucket) without touching application code.
     */
    'disk_map' => [
        'local' => env('DOCUMENT_STORAGE_LOCAL_DISK', 'public'),
        's3' => env('DOCUMENT_STORAGE_S3_DISK', 's3'),
    ],

    /*
     |--------------------------------------------------------------------------
     | S3 temporary URL configuration
     |--------------------------------------------------------------------------
     |
     | When serving downloads directly from S3, a signed URL is generated. This
     | value controls the default time-to-live (in seconds) for those URLs.
     */
    's3' => [
        'temporary_url_ttl' => (int) env('DOCUMENT_STORAGE_S3_URL_TTL', 604800),
    ],
];
