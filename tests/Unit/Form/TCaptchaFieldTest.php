<?php
declare(strict_types=1);

namespace Tests\Unit\Form;

use Aurea\Core\TSession;
use Aurea\Form\TCaptchaField;
use PHPUnit\Framework\TestCase;

/** @covers \Aurea\Form\TCaptchaField */
final class TCaptchaFieldTest extends TestCase
{
    /** @var array<string,mixed> */
    private array $postBackup = [];
    /** @var array<string,mixed> */
    private array $getBackup = [];
    /** @var array<string,mixed> */
    private array $sessionBackup = [];

    protected function setUp(): void
    {
        $this->postBackup = $_POST;
        $this->getBackup = $_GET;
        $this->sessionBackup = $_SESSION ?? [];
        TSession::free();
    }

    protected function tearDown(): void
    {
        $_POST = $this->postBackup;
        $_GET = $this->getBackup;
        $_SESSION = $this->sessionBackup;
        TSession::free();
    }

    public function testGetContentsRendersQuestionAndInput(): void
    {
        $c = new TCaptchaField('cap');
        $html = $c->getContents();
        $this->assertStringContainsString('captcha-question', $html);
        $this->assertStringContainsString('name="cap"', $html);
        $stored = TSession::getValue('captcha_cap');
        $this->assertIsArray($stored);
        $this->assertArrayHasKey('answer', $stored);
        $this->assertStringContainsString($stored['a'] . ' + ' . $stored['b'] . ' = ?', $html);
    }

    public function testValidateWithCorrectAnswer(): void
    {
        $c = new TCaptchaField('cap');
        $c->getContents();
        $stored = TSession::getValue('captcha_cap');
        $_POST['cap'] = (string) $stored['answer'];
        $c->validate();
        $this->assertTrue(true);
    }

    public function testValidateWithWrongAnswerThrows(): void
    {
        $c = new TCaptchaField('cap');
        $c->getContents();
        $stored = TSession::getValue('captcha_cap');
        $_POST['cap'] = (string) ((int) $stored['answer'] + 100);
        $this->expectException(\Exception::class);
        $c->validate();
    }

    public function testValidateWithoutAnswerThrows(): void
    {
        $c = new TCaptchaField('cap');
        $c->getContents();
        unset($_POST['cap'], $_GET['cap']);
        $this->expectException(\Exception::class);
        $c->validate();
    }

    public function testSetValueIsIgnored(): void
    {
        $c = new TCaptchaField('cap');
        $this->assertSame($c, $c->setValue('qualquer'));
        $c->getContents();
        $stored = TSession::getValue('captcha_cap');
        $_POST['cap'] = (string) $stored['answer'];
        $c->setValue('outro-valor-ignorado');
        $c->validate();
        $this->assertTrue(true);
    }
}
