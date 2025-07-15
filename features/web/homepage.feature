Feature: Homepage
  In order to access the application
  As a user
  I want to be able to visit the homepage

  Scenario: Visit homepage
    Given I am on the homepage
    Then I should see "Homepage content"
    And the response status should be 200

  Scenario: Navigate to different pages
    Given I am on the homepage
    When I click on "About"
    Then I should be on "/about"
    And I should see "Content after clicking About"

  Scenario: Submit a contact form
    Given I am on the "contact" page
    When I submit the form with:
      | name    | John Doe           |
      | email   | john@example.com   |
      | message | Test message       |
    Then I should see "Form submitted with data"
    And the response status should be 200
