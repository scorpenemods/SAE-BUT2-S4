<?php

use PHPUnit\Framework\TestCase;

require_once '../Model/Database.php';

class DatabaseTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
    }

    public function testAddUser()
    {
        $result = $this->db->addUser(
            'test@example.com',
            'password123',
            '0123456789',
            'John',
            'Activité',
            'Role',
            'Doe',
            1
        );
        $this->assertTrue($result, 'Failed to add user');
    }

    public function testVerifyLogin()
    {
        $user = $this->db->verifyLogin('test@example.com', 'password123');
        $this->assertNotEmpty($user, 'Login verification failed');
        $this->assertArrayHasKey('valid_email', $user);
        $this->assertArrayHasKey('status_user', $user);
    }

    public function testGetPersonByUsername()
    {
        $person = $this->db->getPersonByUsername('test@example.com');
        $this->assertNotNull($person, 'Failed to retrieve person by username');
        $this->assertEquals('John', $person->getPrenom());
    }

    public function testSendMessage()
    {
        $messageId = $this->db->sendMessage(1, 2, 'Hello, this is a test message');
        $this->assertNotFalse($messageId, 'Failed to send message');
    }

    public function testGetMessages()
    {
        $messages = $this->db->getMessages(1, 2);
        $this->assertNotEmpty($messages, 'Failed to retrieve messages between users');
    }

    public function testUpdateUserPasswordByEmail()
    {
        $result = $this->db->updateUserPasswordByEmail('test@example.com', password_hash('newpassword123', PASSWORD_DEFAULT));
        $this->assertTrue($result, 'Failed to update user password');
    }

    public function testGetUnreadNotificationCount()
    {
        $count = $this->db->getUnreadNotificationCount(1);
        $this->assertIsInt($count, 'Unread notification count is not an integer');
    }

    public function testAddNotification()
    {
        $result = $this->db->addNotification(1, 'This is a test notification', 'info');
        $this->assertTrue($result, 'Failed to add notification');
    }

    public function testGetNotifications()
    {
        $notifications = $this->db->getNotifications(1);
        $this->assertNotEmpty($notifications, 'Failed to retrieve notifications');
    }

    public function testDeleteUser()
    {
        $result = $this->db->deleteUser(1);
        $this->assertTrue($result, 'Failed to delete user');
    }

    protected function tearDown(): void
    {
        $this->db->closeConnection();
    }
}

?>
