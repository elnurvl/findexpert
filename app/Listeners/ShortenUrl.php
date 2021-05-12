<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShortenUrl implements ShouldQueue
{
    private $waitFor;

    /**
     * Create the event listener.
     *
     * @param $waitFor
     */
    public function __construct($waitFor = 5000)
    {
        //
        $this->waitFor = $waitFor;
    }

    /**
     * Handle the event.
     *
     * @param Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if ($event->user->website == null) return;

        try {
            $response = Http::retry(3, $this->waitFor)->get(getenv('CUTTLY_API_URL'), [
                'short' => $event->user->website,
                'key' => getenv('CUTTLY_API_KEY')
            ]);
        } catch (RequestException $e) {
            // Log if unable to reach the service
            Log::error('Cannot reach CUTTLY');
            return;
        }

        // Check if the entered url is already shortened
        if ($response['url']['status'] == 1) {
            $event->user->update([
                'shortening' => $event->user->website
            ]);
        }

        // Log if Cuttly does not accept the API key
        if ($response['url']['status'] == 4) {
            Log::error('Invalid Cuttly API key');
        }

        // Do not do anything in case of invalid request
        if ($response['url']['status'] != 7) return;

        // Update the user profile if the request returned a shortened link
        $event->user->update([
            'shortening' => $response['url']['shortLink']
        ]);
    }
}
