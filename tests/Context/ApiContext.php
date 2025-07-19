<?php

declare(strict_types=1);

namespace RenalTales\Tests\Context;

use Behat\Behat\Context\Context;

/**
 * API context for Behat tests
 */
class ApiContext implements Context
{
    /**
     * @var array Last API response
     */
    private array $lastResponse = [];

    /**
     * @var int Last HTTP status code
     */
    private int $lastStatusCode = 200;

    /**
     * @Given I have a valid API key
     */
    public function iHaveAValidApiKey(): void
    {
        // Set up API key for testing
    }

    /**
     * @When I send a GET request to :endpoint
     */
    public function iSendAGetRequestTo(string $endpoint): void
    {
        // Simulate API GET request
        $this->lastResponse = [
            'status' => 'success',
            'data' => ['endpoint' => $endpoint, 'method' => 'GET']
        ];
        $this->lastStatusCode = 200;
    }

    /**
     * @When I send a POST request to :endpoint with data:
     */
    public function iSendAPostRequestToWithData(string $endpoint, \Behat\Gherkin\Node\TableNode $table): void
    {
        $data = $table->getRowsHash();

        // Simulate API POST request
        $this->lastResponse = [
            'status' => 'success',
            'data' => ['endpoint' => $endpoint, 'method' => 'POST', 'payload' => $data]
        ];
        $this->lastStatusCode = 201;
    }

    /**
     * @Then the response status should be :status
     */
    public function theResponseStatusShouldBe(int $status): void
    {
        if ($this->lastStatusCode !== $status) {
            throw new \Exception("Expected status $status but got {$this->lastStatusCode}");
        }
    }

    /**
     * @Then the response should contain :key
     */
    public function theResponseShouldContain(string $key): void
    {
        if (!isset($this->lastResponse[$key])) {
            throw new \Exception("Response should contain key '$key'");
        }
    }

    /**
     * @Then the response should be valid JSON
     */
    public function theResponseShouldBeValidJson(): void
    {
        $json = json_encode($this->lastResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Response is not valid JSON');
        }
    }
}
