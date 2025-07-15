Feature: Language Switching
  In order to use the application in my preferred language
  As a user
  I want to be able to switch between languages

  Background:
    Given the application supports multiple languages
    And there are languages in the database
    And the default language is "en"

  Scenario: Switch language successfully
    Given the current language is "en"
    When I switch to language "sk"
    Then the current language should be "sk"
    And the interface should be displayed in "sk"

  Scenario: Language preference persists
    Given the current language is "en"
    When I switch to language "de"
    Then the current language should be "de"
    And language preferences should be remembered

  Scenario: Invalid language code
    Given the current language is "en"
    When I switch to language "invalid"
    Then I should not be able to switch to "invalid"
    And the current language should be "en"

  Scenario: Language validation
    Then the language "en" should be valid
    And the language "sk" should be valid
    And the language "xyz" should be invalid

  Scenario: Fallback to default language
    Given the current language is "en"
    Then the fallback language should be "en"
    And missing translations should fall back to "en"
