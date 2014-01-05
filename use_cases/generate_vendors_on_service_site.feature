Feature: generate link of compressed vendors folder from composer.json
  As a developer
  I want composer to run faster
  Because I want to save time

  @javascript
  Scenario: Generate vendor .zip download link
    Given I am on the homepage
     When I fill in "form_body" with:
      """
      {
          "require": {
              "symfony/yaml": "~2.3"
          },
          "require-dev": {
              "symfony/filesystem": "~2.3"
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
