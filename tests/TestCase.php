<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @var Client
     */
    protected $neo4jClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup the Neo4j client for graph database queries
        $this->neo4jClient = ClientBuilder::create()
            ->addHttpConnection('default',
                env('NEO4J_PROTOCOL').'://'.env('NEO4J_USER').':'.env('NEO4J_PASSWORD').'@'.env('NEO4J_HOST'))
            ->setDefaultConnection('default')
            ->build();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Refresh the graph database
        // TODO: A temporary workaround. Use the library method instead of this request.
        Http::withBasicAuth(env('NEO4J_USER'), env('NEO4J_PASSWORD'))
            ->post(env('NEO4J_PROTOCOL').'://'.env('NEO4J_HOST')."/db/data/transaction/commit", [
            'statements' => [
                [
                    'statement' => <<<'CYPHER'
MATCH (n) DETACH DELETE n
CYPHER
                ]
            ]
        ]);

        // TODO: Use this instead of the above request.
        // TODO: Neo4j Client does not make changes on the database. Possibly a library bug.
//        $this->neo4jClient->run(<<<CYPHER
//MATCH (n) DETACH DELETE n
//CYPHER
//        );
    }
}
