<?php

declare(strict_types=1);

namespace RenalTales\Tests\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use RenalTales\Core\Application;
use RenalTales\Tests\TestCase;

/**
 * Web context for Behat tests
 */
class WebContext implements Context
{
    /**
     * @var Application The application instance
     */
    private Application $app;

    /**
     * @var array Response data
     */
    private array $response = [];

    /**
     * @var string Current URL
     */
    private string $currentUrl = '';

    /**
     * Initialize context
     */
    public function __construct()
    {
        $this->app = new Application();
        $this->app->bootstrap();
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(BeforeScenarioScope $scope): void
    {
        $this->response = [];
        $this->currentUrl = '';
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(AfterScenarioScope $scope): void
    {
        // Cleanup after scenario
    }

    /**
     * @Given I am on the homepage
     */
    public function iAmOnTheHomepage(): void
    {
        $this->currentUrl = '/';
        $this->response = [
            'status' => 200,
            'content' => 'Homepage content',
            'headers' => ['Content-Type' => 'text/html']
        ];
    }

    /**
     * @Given I am on the :page page
     */
    public function iAmOnThePage(string $page): void
    {
        $this->currentUrl = '/' . $page;
        $this->response = [
            'status' => 200,
            'content' => ucfirst($page) . ' page content',
            'headers' => ['Content-Type' => 'text/html']
        ];
    }

    /**
     * @When I visit :url
     */
    public function iVisit(string $url): void
    {
        $this->currentUrl = $url;
        $this->response = [
            'status' => 200,
            'content' => 'Page content for ' . $url,
            'headers' => ['Content-Type' => 'text/html']
        ];
    }

    /**
     * @When I click on :link
     */
    public function iClickOn(string $link): void
    {
        // Simulate clicking on a link
        $this->currentUrl = '/' . strtolower($link);
        $this->response = [
            'status' => 200,
            'content' => 'Content after clicking ' . $link,
            'headers' => ['Content-Type' => 'text/html']
        ];
    }

    /**
     * @When I submit the form with:
     */
    public function iSubmitTheFormWith(\Behat\Gherkin\Node\TableNode $table): void
    {
        $data = $table->getRowsHash();

        // Simulate form submission
        $this->response = [
            'status' => 200,
            'content' => 'Form submitted with data: ' . json_encode($data),
            'headers' => ['Content-Type' => 'text/html']
        ];
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee(string $text): void
    {
        if (strpos($this->response['content'], $text) === false) {
            throw new \Exception("Text '$text' not found in response");
        }
    }

    /**
     * @Then I should not see :text
     */
    public function iShouldNotSee(string $text): void
    {
        if (strpos($this->response['content'], $text) !== false) {
            throw new \Exception("Text '$text' found in response but should not be there");
        }
    }

    /**
     * @Then I should be on :url
     */
    public function iShouldBeOn(string $url): void
    {
        if ($this->currentUrl !== $url) {
            throw new \Exception("Expected to be on '$url' but currently on '{$this->currentUrl}'");
        }
    }

    /**
     * @Then the response status should be :status
     */
    public function theResponseStatusShouldBe(int $status): void
    {
        if ($this->response['status'] !== $status) {
            throw new \Exception("Expected status $status but got {$this->response['status']}");
        }
    }

    /**
     * @Then I should see the following elements:
     */
    public function iShouldSeeTheFollowingElements(\Behat\Gherkin\Node\TableNode $table): void
    {
        foreach ($table->getRows() as $row) {
            $element = $row[0];
            $this->iShouldSee($element);
        }
    }

    /**
     * @Then the page should contain a form
     */
    public function thePageShouldContainAForm(): void
    {
        $this->iShouldSee('form');
    }

    /**
     * @Then the page should contain a link to :url
     */
    public function thePageShouldContainALinkTo(string $url): void
    {
        $this->iShouldSee($url);
    }
}
