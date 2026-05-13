<?php

namespace App\Services;

use GuzzleHttp\Client;

class SupabaseService
{
    protected $url;
    protected $anonKey;
    protected $serviceKey;

    public function __construct()
    {
        $this->url        = env('SUPABASE_URL');
        $this->anonKey    = env('SUPABASE_ANON_KEY');
        $this->serviceKey = env('SUPABASE_SERVICE_ROLE_KEY');
    }

    public function getAdminClient()
    {
        return new Client([
            'base_uri' => $this->url,
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->serviceKey,
                'apikey'        => $this->serviceKey,
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function storage()
    {
        return new SupabaseStorage($this);
    }
}