@push('endHead')
    <script id="unlayer" src="https://editor.unlayer.com/embed.js" defer></script>
@endpush

<script>
    window.unlayerInitialized = false;

    document.getElementById('unlayer').addEventListener('load', initUnlayer);

    document.addEventListener('turbo:before-visit', confirmBeforeLeaveAndDestroyUnlayer);
    document.addEventListener("turbo:load", initUnlayer);
    window.addEventListener('beforeunload', confirmBeforeLeaveAndDestroyUnlayer);

    function loadTemplate() {
        document.getElementById('unlayer_template_error').classList.add('hidden');
        const slug = document.getElementById('unlayer_template').value;

        fetch('https://api.graphql.unlayer.com/graphql', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                query: `
                    query StockTemplateLoad($slug: String!) {
                      StockTemplate(slug: $slug) {
                        StockTemplatePages {
                          design
                        }
                      }
                    }
                  `,
                variables: {
                    slug: slug,
                },
            }),
        })
            .then((res) => res.json())
            .then((result) => {
                if (! result.data.StockTemplate) {
                    document.getElementById('unlayer_template_error').innerHTML = '{{ __('mailcoach - Template not found.') }}';
                    document.getElementById('unlayer_template_error').classList.remove('hidden');
                    return;
                }

                unlayer.loadDesign(result.data.StockTemplate.StockTemplatePages[0].design);
                document.querySelector('[data-modal="load-unlayer-template"]').dispatchEvent(new Event('dismiss'));
            });
    }

    function initUnlayer() {
        document.getElementById('load-template').addEventListener('click', loadTemplate);

        if (window.unlayerInitialized || unlayer.init === undefined) {
            return;
        }

        unlayer.init(@json($options));

        window.unlayerInitialized = true;

        unlayer.loadDesign({!! $structuredHtml !!});

        unlayer.registerCallback('image', (file, done) => {
            let data = new FormData();
            data.append('file', file.attachments[0]);

            fetch('{{ route('mailcoach-unlayer.upload') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: data
            }).then(response => {
                // Make sure the response was valid
                if (response.status >= 200 && response.status < 300) {
                    return response.json()
                }

                let error = new Error(response.statusText);
                error.response = response;
                throw error
            }).then(data => done({ progress: 100, url: data.url }))
        });

        const mergeTags = {};
        @foreach ($replacers as $replacerName => $replacerDescription)
            mergeTags["{{ $replacerName }}"] = {
                name: "{{ $replacerName }}",
                value: "::{{ $replacerName }}::"
            };
        @endforeach

        unlayer.setMergeTags(mergeTags);

        document.getElementById('save').addEventListener('click', event => {
            event.preventDefault();

            unlayer.exportHtml(function(data) {
                document.getElementById('html').value = data.html;
                document.getElementById('structured_html').value = JSON.stringify(data.design);
                document.getElementById('html').dataset.dirty = "";
                document.querySelector('main form').submit();
            });
        });

        unlayer.addEventListener('design:updated', function(data) {
            document.getElementById('html').dataset.dirty = "dirty";
        });
    }

    function confirmBeforeLeaveAndDestroyUnlayer(event) {
        if (document.getElementById('html').dataset.dirty === "dirty" && ! confirm('Are you sure you want to leave this page? Any unsaved changes will be lost.')) {
            event.preventDefault();
            return;
        }

        window.unlayerInitialized = false;

        document.removeEventListener('turbo:before-visit', confirmBeforeLeaveAndDestroyUnlayer);
        document.removeEventListener("turbo:load", initUnlayer);
        window.removeEventListener('beforeunload', confirmBeforeLeaveAndDestroyUnlayer);
        document.getElementById('load-template').removeEventListener('click', loadTemplate);
    }

</script>
<div class="h-full">
    <div class="form-row max-w-full h-full">
        <label class="label" for="html">{{ __('Body') }}</label>
        @isset($errors)
            @error('html')
                <p class="form-error" role="alert">{{ $message }}</p>
            @enderror
        @endisset
        <div class="overflow-hidden -mx-10 h-full">
            <div id="editor" class="h-full -ml-2 pr-3 py-1" style="min-height: 75vh"></div>
        </div>
        <input type="hidden" name="html" id="html" value="{{ $html }}">
        <input type="hidden" name="structured_html" id="structured_html" value="{{ json_encode($structuredHtml) }}">
    </div>
</div>

<div class="form-buttons">
    <x-mailcoach::button id="save" :label="__('Save content')"/>
    @if ($showTestButton)
        <x-mailcoach::button-secondary data-modal-trigger="send-test" :label="__('Send Test')"/>
    @endif
    <x-mailcoach::button-secondary data-modal-trigger="load-unlayer-template" :label="__('mailcoach - Load Unlayer template')"/>
</div>

@push('modals')
    <x-mailcoach::modal :title="__('mailcoach - Load Unlayer template')" name="load-unlayer-template">
        <p class="mb-4">{!! __('mailcoach - You can load an <a class="text-blue-500" href="https://unlayer.com/templates" target="_blank">Unlayer template</a> by entering the slug.') !!}</p>

        <x-mailcoach::text-field label="Unlayer template" name="unlayer_template" />
        <p id="unlayer_template_error" class="form-error hidden mt-1" role="alert"></p>

        <div class="form-buttons">
            <x-mailcoach::button class="mt-auto ml-2" id="load-template" label="Load" type="button" />
            <x-mailcoach::button-cancel :label=" __('mailcoach - Cancel')" />
        </div>
    </x-mailcoach::modal>
@endpush
