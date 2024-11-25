<?php

namespace App\Notifications\Server;

use App\Models\Server;
use App\Notifications\Channels\DiscordChannel;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Dto\DiscordMessage;
use App\Notifications\Dto\SlackMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DockerCleanup extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public function __construct(public Server $server, public string $message)
    {
        $this->onQueue('high');
    }

    public function via(object $notifiable): array
    {
        $channels = [];
        // $isEmailEnabled = isEmailEnabled($notifiable);
        $isDiscordEnabled = data_get($notifiable, 'discord_enabled');
        $isTelegramEnabled = data_get($notifiable, 'telegram_enabled');
        $isSlackEnabled = data_get($notifiable, 'slack_enabled');
        if ($isDiscordEnabled) {
            $channels[] = DiscordChannel::class;
        }
        // if ($isEmailEnabled) {
        //     $channels[] = EmailChannel::class;
        // }
        if ($isTelegramEnabled) {
            $channels[] = TelegramChannel::class;
        }
        if ($isSlackEnabled) {
            $channels[] = SlackChannel::class;
        }

        return $channels;
    }

    // public function toMail(): MailMessage
    // {
    //     $mail = new MailMessage();
    //     $mail->subject("Coolify: Server ({$this->server->name}) high disk usage detected!");
    //     $mail->view('emails.high-disk-usage', [
    //         'name' => $this->server->name,
    //         'disk_usage' => $this->disk_usage,
    //         'threshold' => $this->docker_cleanup_threshold,
    //     ]);
    //     return $mail;
    // }

    public function toDiscord(): DiscordMessage
    {
        return new DiscordMessage(
            title: ':white_check_mark: Server cleanup job done',
            description: $this->message,
            color: DiscordMessage::successColor(),
        );
    }

    public function toTelegram(): array
    {
        return [
            'message' => "Coolify: Server '{$this->server->name}' cleanup job done!\n\n{$this->message}",
        ];
    }

    public function toSlack(): SlackMessage
    {
        return new SlackMessage(
            title: 'Server cleanup job done',
            description: "Server '{$this->server->name}' cleanup job done!\n\n{$this->message}",
            color: SlackMessage::successColor()
        );
    }
}
