<?php

namespace Spatie\MailcoachUnlayer\Tests;

use Livewire\Livewire;
use Spatie\Mailcoach\Domain\Campaign\Models\Template;

class UnlayerEditorTest extends TestCase
{
    /** @test * */
    public function it_renders_a_view()
    {
        $template = Template::factory()->create();
        Livewire::test('mailcoach-unlayer::editor', ['model' => $template])
            ->assertSee('unlayer.init');
    }

    /** @test * */
    public function test_passes_along_configured_options()
    {
        config(['mailcoach.unlayer.options' => [
            'appearance' => ['theme' => 'dark'],
        ]]);

        $template = Template::factory()->create();
        Livewire::test('mailcoach-unlayer::editor', ['model' => $template])
            ->assertSee('appearance')
            ->assertSee('dark');
    }
}
