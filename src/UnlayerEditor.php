<?php

namespace Spatie\MailcoachUnlayer;

use Spatie\Mailcoach\Models\Concerns\HasHtmlContent;
use Spatie\Mailcoach\Support\Editor\Editor;
use Spatie\Mailcoach\Support\Replacers\ReplacerWithHelpText;

class UnlayerEditor implements Editor
{
    public function render(HasHtmlContent $model): string
    {
        $replacers = collect(config('mailcoach.replacers'))
            ->map(fn (string $className) => app($className))
            ->flatMap(fn (ReplacerWithHelpText $replacer) => $replacer->helpText())
            ->toArray();

        $options = array_merge_recursive([
            'id' => 'editor',
            'displayMode' => 'email',
            'features'=> ['textEditor' => ['spellChecker' => true]],
            'tools'=> ['form' => ['enabled' => false]],
        ], config('mailcoach.unlayer.options', []));

        return view('mailcoach-unlayer::unlayer')
            ->with([
                'html' => old('html', $model->getHtml()),
                'structuredHtml' => old('structured_html', $model->getStructuredHtml()),
                'replacers' => $replacers,
                'options' => $options,
            ])
            ->render();
    }
}
