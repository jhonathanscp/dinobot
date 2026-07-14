<?php

namespace Tests\Feature;

use App\Jobs\ProcessGameCommandJob;
use App\Services\MessageService;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use Tests\TestCase;

class WebhookRouterTest extends TestCase
{
    public function test_it_dispatches_game_job_only_for_allowed_group(): void
    {
        Bus::fake();

        config([
            'app.game_prefix' => '$',
            'app.game_allowed_group_jid' => '120363406476793354@g.us',
        ]);

        $payload = $this->buildPayload('$s', '120363406476793354@g.us');

        $response = $this->postJson('/api/webhook', $payload);

        $response->assertOk()->assertJson(['status' => 'EVENT_RECEIVED']);
        Bus::assertDispatched(ProcessGameCommandJob::class);
    }

    public function test_it_silently_ignores_game_command_outside_allowed_group(): void
    {
        Bus::fake();

        config([
            'app.game_prefix' => '$',
            'app.game_allowed_group_jid' => '120363406476793354@g.us',
        ]);

        $payload = $this->buildPayload('$s', '553171419903@s.whatsapp.net');

        $response = $this->postJson('/api/webhook', $payload);

        $response->assertOk()->assertJson(['status' => 'ignored']);
        Bus::assertNotDispatched(ProcessGameCommandJob::class);
    }

    public function test_it_keeps_bang_flow_unchanged(): void
    {
        Bus::fake();

        config([
            'app.prefix' => '!',
            'app.game_prefix' => '$',
            'app.game_allowed_group_jid' => '120363406476793354@g.us',
        ]);

        $this->mock(MessageService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('processMessage')->once()->andReturnTrue();
        });

        $payload = $this->buildPayload('!help', '120363406476793354@g.us');

        $response = $this->postJson('/api/webhook', $payload);

        $response->assertOk()->assertJson(['status' => 'EVENT_RECEIVED']);
        Bus::assertNotDispatched(ProcessGameCommandJob::class);
    }

    private function buildPayload(string $message, string $remoteJid): array
    {
        return [
            'event' => 'messages.upsert',
            'data' => [
                'key' => [
                    'remoteJid' => $remoteJid,
                    'fromMe' => false,
                ],
                'message' => [
                    'conversation' => $message,
                ],
            ],
        ];
    }
}
