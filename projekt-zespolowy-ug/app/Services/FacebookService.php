<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FacebookService
{
    private string $verifyToken;
    private string $pageAccessToken;
    private Client $client;

    public function __construct(Client $client)
    {
        $this->verifyToken = config('services.facebook.fb_verify_token');
        $this->pageAccessToken = config('services.facebook.fb_page_access_token');
        $this->client = $client;
    }


    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function verifyWebhook(Request $request): Application|Response|ResponseFactory
    {
        if ($request->hub_verify_token === $this->verifyToken) {
            return response($request->hub_challenge, 200);
        }

        return response('Unauthorized', 403);
    }

    /**
     * @param $recipientId
     * @param $message
     * @return void
     * @throws GuzzleException
     */
    public function sendMessage($recipientId, $message): void
    {
        $url = "https://graph.facebook.com/v13.0/me/messages?access_token=$this->pageAccessToken";

        $this->client->post($url, [
            'json' => [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $message],
            ],
        ]);
    }
}
