@local @local_chuspasocial @javascript
Feature: Publish a post on the general wall
  In order to share information with other users
  As a student
  I want to publish a post on the general wall

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Curso 1  | CURSO1    | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | CURSO1 | student |

  Scenario: A student publishes a post on the general wall
    Given I log in as "student1"
    And I visit "/local/chuspasocial/index.php"
    When I set the field "Post content" to "Publicacion de prueba en el muro general"
    And I press "Publish"
    Then I should see "Publicacion de prueba en el muro general"
    And I should see "Publicacion de prueba en el muro general" in the ".local_chuspasocial_wall > .post-item:first-child" "css_element"
