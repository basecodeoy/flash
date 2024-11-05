<?php

declare(strict_types=1);

namespace BaseCodeOy\Flash;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Traits\Macroable;

/** @mixin \BaseCodeOy\Flash\Message */
final class Flash
{
    use Macroable;

    protected Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function __get(string $name)
    {
        return $this->getMessage()->{$name} ?? null;
    }

    public static function levels(array $methods): void
    {
        foreach ($methods as $level => $config) {
            self::macro($level, fn (string $message, ?string $title = '') => $this->flashMessage(new Message($message, $title, $level, $config)));
        }
    }

    public static function fromConfig(): void
    {
        self::levels(config('flash.levels'));
    }

    public function getMessage(): ?Message
    {
        $flashedMessageProperties = $this->session->get('laravel_flash_message');

        if (!$flashedMessageProperties) {
            return null;
        }

        return new Message(
            $flashedMessageProperties['message'],
            $flashedMessageProperties['title'],
            $flashedMessageProperties['level'],
            $flashedMessageProperties['config'],
        );
    }

    public function flash(Message $message): void
    {
        if ($message->level && self::hasMacro($message->level)) {
            $methodName = $message->level;

            $this->{$methodName}($message->message);

            return;
        }

        $this->flashMessage($message);
    }

    public function flashMessage(Message $message): void
    {
        $this->session->flash('laravel_flash_message', $message->toArray());
    }
}
