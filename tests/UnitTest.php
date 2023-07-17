<?php

namespace Hanwoolderink\Flow\Tests;

use Hanwoolderink\Flow\Action;
use Hanwoolderink\Flow\ActionDispatcher;
use Hanwoolderink\Flow\Flow;
use Hanwoolderink\Flow\FlowProvider;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue as FacadesQueue;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use PHPUnit\Framework\TestCase;

class User
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {
    }
}

class NotifyMail extends Mailable
{
    public function __construct(
        public string $message,
    ) {
    }
}

class CreateUser extends Action
{
    public function handle(string $name, string $email, string $password): User
    {
        return new User($name, $email, $password);
    }

    public function authorize(): bool
    {
        return true;
    }
}

class Greet extends Action
{
    public function handle(User $user): string
    {
        return "Hello {$user->name}";
    }
}

class Notify extends Action
{
    public function handle(User $user, string $message): void
    {
        Mail::to($user->email)->send(new NotifyMail($message));
    }
}

class UnitTest extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FlowProvider::class,
        ];
    }

    public function testTrueIsTrue(): void
    {
        FacadesQueue::fake();
        Mail::fake();

        $flow = new Flow();

        $flow
            ->action(CreateUser::class, [
                'name' => 'John Doe',
                'email' => 'john@doe.nl',
                'password' => 'password',
            ])
            ->action(
                Greet::class,
                fn (Flow $f) => ['user' => $f->getResult(CreateUser::class)]
            )
            ->action(
                Notify::class,
                fn (Flow $f) => [
                    'message' => $f->getResult(Greet::class),
                    'user' => $f->getResult(CreateUser::class),
                ],
                true
            )
            ->run();

        $this->assertEquals('Hello John Doe', $flow->getResult(Greet::class));

        FacadesQueue::assertPushed(ActionDispatcher::class, function (ActionDispatcher $job) {
            // execute the job to test the mail is sent
            app()->call([$job, 'handle']);

            return $job->getAction() === Notify::class;
        });

        Mail::assertSent(NotifyMail::class, function (NotifyMail $mail) {
            return $mail->message === 'Hello John Doe';
        });
    }
}
