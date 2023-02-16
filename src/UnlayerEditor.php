<?php

namespace Spatie\MailcoachUnlayer;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Audience\Models\Tag;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMail;
use Spatie\Mailcoach\Domain\Automation\Support\Replacers\ReplacerWithHelpText as AutomationMailReplacerWithHelpText;
use Spatie\Mailcoach\Domain\Campaign\Enums\TagType;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Campaign\Models\Concerns\HasHtmlContent;
use Spatie\Mailcoach\Domain\Campaign\Rules\HtmlRule;
use Spatie\Mailcoach\Domain\Campaign\Support\Replacers\ReplacerWithHelpText as CampaignReplacerWithHelpText;
use Spatie\Mailcoach\Http\App\Livewire\EditorComponent;

class UnlayerEditor extends EditorComponent
{
    public function mount(HasHtmlContent $model)
    {
        parent::mount($model);
        $this->template = null;
        $this->templateId = null;
    }

    public function rules(): array
    {
        return [
            'templateFieldValues.html' => ['required', new HtmlRule()],
        ];
    }

    public function saveQuietly()
    {
        $this->fullHtml = str_replace('&#91;&#91;&#91;', '[[[', $this->fullHtml);
        $this->fullHtml = str_replace('&#93;&#93;&#93;', ']]]', $this->fullHtml);

        parent::saveQuietly();
    }

    public function render(): View
    {
        $this->templateFieldValues['html'] ??= '';
        $this->templateFieldValues['json'] ??= '';

        $replacers = match ($this->model::class) {
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
            'specialLinks' => $this->getSpecialLinks($this->model),
        ], config('mailcoach.unlayer.options', []));

        return view('mailcoach-unlayer::unlayer', [
            'replacers' => $replacers,
            'options' => $options,
        ]);
    }

    protected function filterNeededFields(array $fields, \Spatie\Mailcoach\Domain\Campaign\Models\Template|null $template): array
    {
        return Arr::only($fields, ['html', 'json']);
    }

    private function getSpecialLinks(HasHtmlContent $model): array
    {
        $links = [
            ['name' => 'Webview URL', 'href' => '{{webviewUrl}}', 'target' => '_blank'],
            ['name' => 'Manage preferences', 'href' => '{{preferencesUrl}}', 'target' => '_blank'],
            ['name' => 'Unsubscribe URL', 'href' => '{{unsubscribeUrl}}', 'target' => '_blank'],
        ];

        if ($model instanceof Campaign && $model->emailList) {
            $tags = $model->emailList->tags()->where('type', TagType::Default)->get();

            $unsubscribeLinks = [];
            $tags->each(function (Tag $tag) use (&$unsubscribeLinks) {
                $unsubscribeLinks[] = [
                    'name' => $tag->name,
                    'href' => "{{unsubscribeTag['{$tag->name}']}}",
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
