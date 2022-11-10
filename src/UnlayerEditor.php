<?php

namespace Spatie\MailcoachUnlayer;

use Spatie\Mailcoach\Domain\Audience\Models\Tag;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMail;
use Spatie\Mailcoach\Domain\Automation\Support\Replacers\ReplacerWithHelpText as AutomationMailReplacerWithHelpText;
use Spatie\Mailcoach\Domain\Campaign\Enums\TagType;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Campaign\Models\Concerns\HasHtmlContent;
use Spatie\Mailcoach\Domain\Campaign\Models\Template;
use Spatie\Mailcoach\Domain\Campaign\Support\Replacers\ReplacerWithHelpText as CampaignReplacerWithHelpText;
use Spatie\Mailcoach\Domain\Shared\Support\Editor\Editor;
use Spatie\Mailcoach\Domain\TransactionalMail\Models\TransactionalMailTemplate;

class UnlayerEditor implements Editor
{
    public function render(HasHtmlContent $model): string
    {
        $replacers = match ($model::class) {
            AutomationMail::class => config('mailcoach.automation.replacers'),
            default => config('mailcoach.campaigns.replacers'),
        };

        $replacers = collect($replacers)
            ->map(fn (string $className) => app($className))
            ->flatMap(fn (CampaignReplacerWithHelpText|AutomationMailReplacerWithHelpText $replacer) => $replacer->helpText())
            ->toArray();

        $options = array_merge_recursive([
            'id' => 'editor',
            'displayMode' => 'email',
            'features' => ['textEditor' => ['spellChecker' => true]],
            'tools' => ['form' => ['enabled' => false]],
            'specialLinks' => $this->getSpecialLinks($model),
        ], config('mailcoach.unlayer.options', []));

        return view('mailcoach-unlayer::unlayer')
            ->with([
                'html' => old('html', $model->getHtml()),
                'structuredHtml' => old('structured_html', $model->getStructuredHtml()),
                'replacers' => $replacers,
                'options' => $options,
                'showTestButton' => ! $model instanceof Template && ! $model instanceof TransactionalMailTemplate,
            ])
            ->render();
    }

    private function getSpecialLinks(HasHtmlContent $model): array
    {
        $links = [
            ['name' => 'Unsubscribe URL', 'href' => '::unsubscribeUrl::', 'target' => '_blank'],
        ];

        if ($model instanceof Campaign && $model->emailList) {
            $tags = $model->emailList->tags()->where('type', TagType::DEFAULT)->get();

            $unsubscribeLinks = [];
            $tags->each(function (Tag $tag) use (&$unsubscribeLinks) {
                $unsubscribeLinks[] = [
                    'name' => $tag->name,
                    'href' => "::unsubscribeTag::{$tag->name}::",
                    'target' => '_blank',
                ];
            });

            if (count($unsubscribeLinks)) {
                $links[] = [
                    'name' => 'Unsubscribe from tag',
                    'specialLinks' => $unsubscribeLinks,
                ];
            }
        }

        return $links;
    }
}
