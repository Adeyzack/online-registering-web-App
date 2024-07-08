<?php
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
    public function testPasswordHashing() {
        $password = "Test@123";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertTrue(password_verify($password, $hashed_password));
    }
}

