<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Model/Database.php';

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Behat\Gherkin\Node\TableNode;

class FeatureContext implements Context
{

    public function __construct()
    {
        require_once __DIR__ . '/../../Model/Database.php';
        $db = Database::getInstance();
        $this->connection = $db->getConnection();
    }

    private ?PDO $connection;
    private $users = [];




    /**
     * @Given I have the following user details:
     */
    public function iHaveTheFollowingUserDetails(TableNode $table)
    {
        $this->users = $table->getHash();
    }

    /**
     * @When I call the addUser function for each user
     */
    public function iCallTheAddUserFunctionForEachUser()
    {
        foreach ($this->users as $user) {

            Database::getInstance()->addUser(
                $user['email'],
                $user['password'],
                $user['telephone'],
                $user['prenom'],
                $user['activite'],
                $user['role'],
                $user['nom'],
                $user['status']
            );
        }
    }

    /**
     * @Then each user should be added to the database
     */
    public function eachUserShouldBeAddedToTheDatabase()
    {
        foreach ($this->users as $user) {
            $stmt = $this->connection->prepare("SELECT * FROM User WHERE email = :email");
            $stmt->execute([':email' => $user['email']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            Assert::assertNotEmpty($result, "User with email " . $user['email'] . " was not added to the database");
        }

    }

    /**
     * @Then each user should have a hashed password in the Password table
     */
    public function eachUserShouldHaveAHashedPasswordInThePasswordTable()
    {
        foreach ($this->users as $user) {
            $stmt = $this->connection->prepare("SELECT * FROM Password P INNER JOIN User U ON P.user_id = U.id WHERE U.email = :email");
            $stmt->execute([':email' => $user['email']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            Assert::assertNotEmpty($result, "Password for user with email " . $user['email'] . " was not found in the Password table");
            Assert::assertTrue(password_verify($user['password'], $result['password_hash']), "Password for user " . $user['email'] . " is not correctly hashed");
        }
    }

    /**
     * @Then each user should have default preferences
     */
    public function eachUserShouldHaveDefaultPreferences()
    {
        foreach ($this->users as $user) {
            $stmt = $this->connection->prepare("SELECT * FROM Preference P INNER JOIN User U ON P.user_id = U.id WHERE U.email = :email");
            $stmt->execute([':email' => $user['email']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            Assert::assertNotEmpty($result, "Preferences for user with email " . $user['email'] . " were not found");
            Assert::assertEquals(1, $result['notification'], "Default notification preference not set correctly for user " . $user['email']);
            Assert::assertEquals(0, $result['a2f'], "Default 2FA preference not set correctly for user " . $user['email']);
            Assert::assertEquals(0, $result['darkmode'], "Default dark mode preference not set correctly for user " . $user['email']);
        }
    }
}