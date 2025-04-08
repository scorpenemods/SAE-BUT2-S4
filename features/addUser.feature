Feature: Add multiple users

  Scenario: Add users with valid data
    Given I have the following user details:
      | email         | password | telephone  | prenom | activite | role  | nom  | status |
      | john@example.com | secret   | 1234567890 | John   | Dev      | 1 | Doe  | 1     |
      | jane@example.com | password | 0987654321 | Jane   | Design   | 4  | Roe  | 1     |
    When I call the addUser function for each user
    Then each user should be added to the database
    Then each user should have a hashed password in the Password table
    Then each user should have default preferences
