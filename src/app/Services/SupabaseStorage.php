<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;

class SupabaseStorage
{
    protected $service;

    public function __construct(SupabaseService $service)
    {
        $this->service = $service;
    }

    public function upload(string $bucket, string $path, $fileContent, string $mimeType = 'image/jpeg')
    {
        try {
            $client = $this->service->getAdminClient();

            $client->request('POST', "/storage/v1/object/{$bucket}/{$path}", [
                'body' => $fileContent,
                'headers' => [
                    'Content-Type' => $mimeType,
                ],
            ]);

            return [
                'success' => true,
                'path' => $path,
                'full_url' => $this->getPublicUrl($bucket, $path),
                'message' => 'Upload thành công',
            ];
        } catch (RequestException $e) {
            $error = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();

            return [
                'success' => false,
                'error' => $error,
            ];
        }
    }

    public function getPublicUrl(string $bucket, string $path): string
    {
        return "{$this->service->getUrl()}/storage/v1/object/public/{$bucket}/{$path}";
    }
}