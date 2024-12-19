<?php

require_once "../vendor/autoload.php";
require_once "..\Model\Utils.php";
use PHPUnit\Framework\TestCase;

class FormatTimestampTest extends TestCase
{
    public function testFormatTimestampToday()
    {
        $timestamp = (new DateTime())->format('Y-m-d H:i:s'); // Timestamp pour aujourd'hui
        $formatted = formatTimestamp($timestamp);

        $this->assertStringStartsWith('Today', $formatted);
        $this->assertMatchesRegularExpression('/Today \d{2}:\d{2}/', $formatted);
    }

    public function testFormatTimestampYesterday()
    {
        $timestamp = (new DateTime('yesterday'))->format('Y-m-d H:i:s'); // Timestamp pour hier
        $formatted = formatTimestamp($timestamp);

        $this->assertStringStartsWith('Yesterday', $formatted);
        $this->assertMatchesRegularExpression('/Yesterday \d{2}:\d{2}/', $formatted);
    }

    public function testFormatTimestampOtherDate()
    {
        $timestamp = '2024-01-01 10:30:00'; // Une date ancienne
        $formatted = formatTimestamp($timestamp);

        $this->assertEquals('01.01.2024 10:30', $formatted);
    }

    public function testFormatTimestampInvalidInput()
    {
        $this->expectException(Exception::class);
        formatTimestamp('invalid-timestamp'); // Une chaîne invalide
    }

    public function testFormatTimestampEdgeCaseMidnight()
    {
        $timestamp = (new DateTime('today midnight'))->format('Y-m-d H:i:s'); // Minuit aujourd'hui
        $formatted = formatTimestamp($timestamp);

        $this->assertStringStartsWith('Today', $formatted);
        $this->assertMatchesRegularExpression('/Today \d{2}:\d{2}/', $formatted);
    }
}
