<?php

namespace Spatie\MailcoachUnlayer\Tests;

use Spatie\Mailcoach\Models\Template;
use Spatie\MailcoachUnlayer\UnlayerEditor;

class UnlayerEditorTest extends TestCase
{
    /** @test * */
    public function it_renders_a_view()
    {
        $editor = new UnlayerEditor();

        $template = factory(Template::class)->create();

        $html = $editor->render($template);

        $this->assertStringContainsString('input type="hidden" name="html"', $html);
        $this->assertStringContainsString('input type="hidden" name="structured_html"', $html);
    }

    /** @test * */
    public function test_passes_along_configured_options()
    {
        config(['mailcoach.unlayer.options' => [
            'appearance' => ['theme' => 'dark']
        ]]);

        $editor = new UnlayerEditor();

        $template = factory(Template::class)->create();

        $html = $editor->render($template);

        $this->assertStringContainsString('appearance', $html);
        $this->assertStringContainsString('dark', $html);
    }
}
