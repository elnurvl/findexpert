<?php

namespace App\Listeners;

use App\Models\Topic;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

class PullHeadings implements ShouldQueue
{
    /**
     * @var Dom
     */
    private $parser;
    /**
     * @var int
     */
    private $waitFor;

    /**
     * Create the event listener.
     *
     * @param Dom $parser
     * @param int $waitFor
     */
    public function __construct(Dom $parser, int $waitFor = 10000)
    {
        $this->parser = $parser;
        $this->waitFor = $waitFor;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $headings = collect();

        // Send a request to the url. Notify user if it cannot be reached
        try {
            $response = Http::retry(3, $this->waitFor)->get($event->user->website);
        } catch (RequestException $e) {
            $event->user->update([
                'failed_to_reach' => true
            ]);
            return;
        }

        // If the request is successful, setup the HTML parser with the fetched content
        try {
            $this->parser->loadStr($response);
        } catch (ChildNotFoundException | CircularException | ContentLengthException | LogicalException | StrictException $e) {
        }

        // Find top level headings
        try {
            collect($this->parser->find("h1"))->toBase()->each(function ($heading) use ($headings, $event) {
                $headings->add(Topic::make([
                    'content' => $heading->text,
                    'tag' => 'h1'
                ]));
            });
        } catch (ChildNotFoundException | NotLoadedException $e) {
        }

        // Find second level headings
        try {
            collect($this->parser->find("h2"))->toBase()->each(function ($heading) use ($headings, $event) {
                $headings->add(Topic::make([
                    'content' => $heading->text,
                    'tag' => 'h2'
                ]));
            });
        } catch (ChildNotFoundException | NotLoadedException $e) {
        }

        // Find third level headings
        try {
            collect($this->parser->find("h3"))->toBase()->each(function ($heading) use ($headings, $event) {
                $headings->add(Topic::make([
                    'content' => $heading->text,
                    'tag' => 'h3'
                ]));
            });
        } catch (ChildNotFoundException | NotLoadedException $e) {
        }

        // If there are no heading tag in the HTML, notify the user
        if ($headings->isNotEmpty()) {
            $event->user->topics()->saveMany($headings);
        } else {
            $event->user->update([
                'no_topic' => true
            ]);
        }
    }
}
