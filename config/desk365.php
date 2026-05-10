<?php

/**
 * Desk365 integration configuration (published stub for consuming apps).
 *
 * Core HTTP keys are read by {@see \Devmatika\Desk365\DTO\ApiConfigDto::fromArray()}.
 * Additional keys are for application-level features (queues, bulk import, KB sync, webhooks).
 *
 * Environment variables (non-exhaustive):
 * - DESK365_BASE_URL: API host (default below)
 * - DESK365_API_KEY / DESK_API_KEY: API key (either name is accepted)
 * - DESK365_API_SECRET / DESK_API_SECRET: optional secret header
 * - DESK365_TIMEOUT, DESK365_RETRY_ATTEMPTS, DESK365_RETRY_BACKOFF, DESK365_API_VERSION
 * - DESK365_FROM_EMAIL
 * - DESK365_INBOUND_SYNC_ENABLED, DESK365_OUTBOUND_SYNC_ENABLED
 * - DESK365_SYNC_EAMS_AT_FIELD, DESK365_TICKET_URL_FIELD, DESK365_OLC_* fields and max lengths
 * - DESK365_QUEUE_SYNC, DESK365_QUEUE_RETRY, REDIS_QUEUE, DESK365_QUEUE_LISTEN_ORDER
 * - DESK365_BULK_SYNC_* (throttling / retries for bulk ticket import)
 * - DESK365_KB_SYNC_* (knowledge base pull throttling and unsynced-run guards)
 * - DESK365_CONVERSATION_WEBHOOK_RESYNC_DELAY_SECONDS
 */
$desk365SyncQueueName = env('DESK365_QUEUE_SYNC') ?? (env('APP_ENV') === 'local' ? 'default' : 'desk365_sync');
$desk365RetryQueueName = env('DESK365_QUEUE_RETRY', 'desk365_retry');
$redisDefaultQueueName = env('REDIS_QUEUE', 'default');
$desk365ListenOrderFromEnv = array_values(array_unique(array_filter(array_map(
    'trim',
    explode(',', (string) env('DESK365_QUEUE_LISTEN_ORDER', ''))
))));
$desk365QueueListenOrderDefault = $desk365ListenOrderFromEnv !== []
    ? $desk365ListenOrderFromEnv
    : [$redisDefaultQueueName, $desk365SyncQueueName, $desk365RetryQueueName];

return [
    /*
    |--------------------------------------------------------------------------
    | Desk365 API (HTTP client)
    |--------------------------------------------------------------------------
    */
    'base_url' => env('DESK365_BASE_URL', 'https://api.desk365.com'),
    'api_key' => env('DESK365_API_KEY', env('DESK_API_KEY', '')),
    'api_secret' => env('DESK365_API_SECRET', env('DESK_API_SECRET')),
    'timeout' => env('DESK365_TIMEOUT', 30),
    'retry_attempts' => env('DESK365_RETRY_ATTEMPTS', 3),
    'retry_backoff' => env('DESK365_RETRY_BACKOFF', 60),
    'version' => env('DESK365_API_VERSION', 'v3'),
    'from_email' => env('DESK365_FROM_EMAIL', 'support@domain.desk365.io'),

    // Optional extra headers for the HTTP client (merged in consuming apps if needed)
    'headers' => null,

    // Default runtime sync flags (tenant / app overrides may supersede)
    'inbound_sync_enabled' => (bool) env('DESK365_INBOUND_SYNC_ENABLED', true),
    'outbound_sync_enabled' => (bool) env('DESK365_OUTBOUND_SYNC_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Custom field names (Desk365 tickets)
    |--------------------------------------------------------------------------
    */
    'sync_eams_at_field' => env('DESK365_SYNC_EAMS_AT_FIELD', 'cf_Sync EAMs At'),
    'ticket_url_field' => env('DESK365_TICKET_URL_FIELD', 'cf_Ticket URL'),
    'olc_detail_field' => env('DESK365_OLC_DETAIL_FIELD', 'cf_OLC Detail'),
    'olc_tracking_events_field' => env('DESK365_OLC_TRACKING_EVENTS_FIELD', 'cf_OLC Tracking Events'),
    'olc_detail_max_length' => (int) env('DESK365_OLC_DETAIL_MAX_LENGTH', 5000),
    'olc_tracking_events_max_length' => (int) env('DESK365_OLC_TRACKING_EVENTS_MAX_LENGTH', 5000),

    /*
    |--------------------------------------------------------------------------
    | Queue names (Laravel / Horizon)
    |--------------------------------------------------------------------------
    | If DESK365_QUEUE_SYNC is not set, local APP_ENV=local uses the default queue so
    | `php artisan queue:work` picks up jobs without a dedicated worker.
    */
    'queue_sync' => $desk365SyncQueueName,
    'queue_retry' => $desk365RetryQueueName,
    'queue_buckets' => [
        'sync' => $desk365SyncQueueName,
        'retry' => $desk365RetryQueueName,
    ],
    'queue_listen_order' => $desk365QueueListenOrderDefault,

    /*
    |--------------------------------------------------------------------------
    | Bulk ticket sync throttling / rate limits
    |--------------------------------------------------------------------------
    */
    'bulk_sync' => [
        'request_delay_ms' => env('DESK365_BULK_SYNC_REQUEST_DELAY_MS', 2000),
        'max_rate_limit_retries' => env('DESK365_BULK_SYNC_MAX_RATE_LIMIT_RETRIES', 5),
        'rate_limit_cooldown_seconds' => env('DESK365_BULK_SYNC_RATE_LIMIT_COOLDOWN_SECONDS', 65),
        'max_request_retries' => max(1, (int) env('DESK365_BULK_SYNC_MAX_REQUEST_RETRIES', 5)),
        'request_retry_base_delay_ms' => max(0, (int) env('DESK365_BULK_SYNC_REQUEST_RETRY_BASE_DELAY_MS', 800)),
    ],

    /*
    |--------------------------------------------------------------------------
    | Knowledge base pull sync (Desk365 KB endpoints)
    |--------------------------------------------------------------------------
    */
    'knowledge_base_sync' => [
        'request_delay_ms' => max(0, (int) env('DESK365_KB_SYNC_REQUEST_DELAY_MS', 600)),
        'rate_limit_cooldown_seconds' => max(1, (int) env('DESK365_KB_SYNC_RATE_LIMIT_COOLDOWN_SECONDS', 65)),
        'max_consecutive_failures_before_abort' => max(1, (int) env('DESK365_KB_SYNC_MAX_CONSECUTIVE_FAILURES_BEFORE_ABORT', 25)),
        'unsynced_max_articles_per_run' => (($v = env('DESK365_KB_SYNC_UNSYNCED_MAX_ARTICLES_PER_RUN')) !== null && $v !== '')
            ? max(1, (int) $v)
            : null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversation webhook → delayed resync
    |--------------------------------------------------------------------------
    */
    'conversation_webhook_resync_delay_seconds' => max(0, (int) env('DESK365_CONVERSATION_WEBHOOK_RESYNC_DELAY_SECONDS', 10)),
];
