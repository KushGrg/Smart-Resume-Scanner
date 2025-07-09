<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Resume Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for resume processing, text extraction,
    | and TF-IDF similarity calculation algorithms.
    |
    */

    'text_extraction' => [
        /*
        |--------------------------------------------------------------------------
        | Supported File Types
        |--------------------------------------------------------------------------
        |
        | List of supported file types for resume uploads.
        |
        */
        'supported_types' => ['pdf', 'doc', 'docx'],

        /*
        |--------------------------------------------------------------------------
        | File Size Limits
        |--------------------------------------------------------------------------
        |
        | Maximum file size for resume uploads (in bytes).
        |
        */
        'max_file_size' => 2 * 1024 * 1024, // 2MB

        /*
        |--------------------------------------------------------------------------
        | Text Processing
        |--------------------------------------------------------------------------
        |
        | Settings for text cleaning and normalization.
        |
        */
        'min_text_length' => 100,
        'max_text_length' => 50000,
        'encoding' => 'UTF-8',
    ],

    'algorithm' => [
        /*
        |--------------------------------------------------------------------------
        | TF-IDF Settings
        |--------------------------------------------------------------------------
        |
        | Configuration for Term Frequency-Inverse Document Frequency algorithm.
        |
        */
        'min_token_length' => 3,
        'max_token_length' => 50,
        'use_stemming' => true,
        'case_sensitive' => false,

        /*
        |--------------------------------------------------------------------------
        | Similarity Thresholds
        |--------------------------------------------------------------------------
        |
        | Score thresholds for categorizing resume matches.
        |
        */
        'similarity_thresholds' => [
            'high' => 0.7,    // High match (70%+)
            'medium' => 0.4,  // Medium match (40-70%)
            'low' => 0.0,     // Low match (0-40%)
        ],

        /*
        |--------------------------------------------------------------------------
        | Stopwords
        |--------------------------------------------------------------------------
        |
        | Enable/disable stopword filtering and custom stopwords.
        |
        */
        'use_stopwords' => true,
        'custom_stopwords' => [
            // Add industry-specific stopwords here
            'resume', 'cv', 'curriculum', 'vitae', 'email', 'phone', 'address',
            'references', 'available', 'upon', 'request',
        ],

        /*
        |--------------------------------------------------------------------------
        | Performance Settings
        |--------------------------------------------------------------------------
        |
        | Settings for performance optimization.
        |
        */
        'cache_vectors' => true,
        'cache_duration' => 3600, // 1 hour in seconds
        'batch_size' => 50,
        'memory_limit' => '256M',
    ],

    'queue' => [
        /*
        |--------------------------------------------------------------------------
        | Queue Settings
        |--------------------------------------------------------------------------
        |
        | Configuration for background job processing.
        |
        */
        'text_extraction' => [
            'queue' => 'text-extraction',
            'timeout' => 300, // 5 minutes
            'tries' => 3,
            'delay' => 0,
        ],

        'similarity_calculation' => [
            'queue' => 'similarity',
            'timeout' => 600, // 10 minutes
            'tries' => 2,
            'delay' => 10, // 10 seconds delay
        ],
    ],

    'scoring' => [
        /*
        |--------------------------------------------------------------------------
        | Scoring Weights
        |--------------------------------------------------------------------------
        |
        | Weights for different components of job posts in similarity calculation.
        |
        */
        'job_title_weight' => 2.0,
        'description_weight' => 1.5,
        'requirements_weight' => 2.5,
        'location_weight' => 0.5,
        'type_weight' => 0.8,
        'experience_weight' => 1.2,

        /*
        |--------------------------------------------------------------------------
        | Ranking Settings
        |--------------------------------------------------------------------------
        |
        | Settings for resume ranking and display.
        |
        */
        'decimal_places' => 4,
        'default_limit' => 50,
        'min_score_display' => 0.1,
    ],

    'analytics' => [
        /*
        |--------------------------------------------------------------------------
        | Analytics and Reporting
        |--------------------------------------------------------------------------
        |
        | Settings for processing analytics and reporting.
        |
        */
        'track_processing_time' => true,
        'track_accuracy_metrics' => true,
        'generate_reports' => true,
        'report_retention_days' => 90,
    ],
];
