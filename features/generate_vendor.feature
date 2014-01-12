Feature: generate link of compressed vendors folder from composer.json
  As a developer
  I want composer to run faster
  Because I want to save time

  @javascript
  Scenario: Generate vendor .zip download link
    Given I am on the homepage
     When I fill in "composer_body" with:
      """
      {
          "require": {
              "monolog/monolog": "1.2.0"
          }
      }
      """
     When I press "Go" after it is ready
      And I wait until the download button shows up
     Then I should see "Validating composer.json"
      And I should see "Sending to queue"
      And I should see "Starting async job"
      And I should see "./composer update"
      And I should see "Checking vulnerability"
      And I should see text matching "Done in \d+\.?\d* seconds"
      And I should see "Download" link

  @javascript @wip
  Scenario: Generate vendor .zip download link
    Given I am on the homepage
    When I fill in "composer_body" with:
    """
      {
          "require": {
              "monolog/monolog": "1.2.0"
          }
      }
      """
    When I press "Go" after it is ready
    And I wait until the download button shows up
    Then I should see "Validating composer.json"
    And I should see "Sending to queue"
    And I should see "Starting async job"
    And I should see "./composer update"
    And I should see "Checking vulnerability"
    And I should see "Serving cached vendor.zip"
    And I should see text matching "Done in \d+\.?\d* seconds"
    And I should see "Download" link

  @javascript
  Scenario: Generate vendor .zip download link
    Given I am on the homepage
    When I fill in "composer_body" with:
    """
      {
          "require": {
              "symfony/routing": "2.0.10"
          }
      }
      """
    When I press "Go" after it is ready
    And I wait until the download button shows up
    Then I should see "Validating composer.json"
    And I should see "Sending to queue"
    And I should see "Starting async job"
    And I should see "./composer update"
    And I should see "Vulnerability found : 1"
    And I should see text matching "Done in \d+\.?\d* seconds"
    And I should see "Download" link