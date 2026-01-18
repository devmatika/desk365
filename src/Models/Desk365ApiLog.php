<?php

namespace Devmatika\Desk365\Models;

use Illuminate\Database\Eloquent\Model;

class Desk365ApiLog extends Model
{
    protected $fillable = [
        'method',
        'endpoint',
        'request_headers',
        'request_body',
        'response_status',
        'response_body',
        'duration_ms',
        'error_message',
        'operation',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'response_status' => 'integer',
        'duration_ms' => 'integer',
    ];
}

