<div class="min-h-screen">
    <script>
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
                        document.getElementById('unlayer_template_error').innerHTML = '{{ __mc('Template not found.') }}';
                        document.getElementById('unlayer_template_error').classList.remove('hidden');
                        return;
                    }

                    unlayer.loadDesign(result.data.StockTemplate.StockTemplatePages[0].design);
                    Alpine.store('modals').close('load-unlayer-template');
                });
        }

        window.init = function() {
            document.getElementById('load-template').addEventListener('click', loadTemplate);

            unlayer.init(@json($options));

            unlayer.loadDesign(JSON.parse(JSON.stringify(this.json)));

            unlayer.registerCallback('image', (file, done) => {
                let data = new FormData();
                data.append('file', file.attachments[0]);

                fetch('{{ action(\Spatie\Mailcoach\Http\Api\Controllers\UploadsController::class) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: data
                })
                .then(response => {
                    // Make sure the response was valid
                    if (response.status >= 200 && response.status < 300) {
                        return response.json()
                    }

                    let error = new Error(response.statusText);
                    error.response = response;
                    throw error
                }).then(data => done({ progress: 100, url: data.file.url }))
            });

            const mergeTags = {};
            @foreach ($replacers as $replacerName => $replacerDescription)
                mergeTags["{{ $replacerName }}"] = {
                    name: "{{ $replacerName }}",
                    value: "::{{ $replacerName }}::"
                };
            @endforeach

            unlayer.setMergeTags(mergeTags);

            const component = this;
            unlayer.addEventListener('design:updated', () => {
                unlayer.exportHtml(function(data) {
                    component.html = data.html;
                    component.json = data.design;
                    document.getElementById('editor').dirty = true;
                });
            });

            unlayer.addEventListener('design:loaded', function(data) {
                unlayer.exportHtml(function(data) {
                    component.html = data.html;
                    component.json = data.design;
                });
            });
        }
    </script>

    <div class="max-w-full">
        <label class="label" for="html">{{ __('Body') }}</label>
        @isset($errors)
            @error('html')
                <p class="form-error" role="alert">{{ $message }}</p>
            @enderror
        @endisset
        <div wire:ignore x-data="{
            html: @entangle('templateFieldValues.html'),
            json: @entangle('templateFieldValues.json'),
            init: init,
        }" class="overflow-hidden -mx-10 h-full">
            <div id="editor" class="h-full -ml-2 pr-3 py-1" style="min-height: 75vh; height: 75vh" data-dirty-check></div>
        </div>
    </div>

    <x-mailcoach::replacer-help-texts :model="$model" />

    <x-mailcoach::editor-buttons :preview-html="$fullHtml" :model="$model">
        <x-mailcoach::button-secondary x-on:click.prevent="$store.modals.open('load-unlayer-template')" :label="__mc('Load Unlayer template')"/>
    </x-mailcoach::editor-buttons>
</div>

@push('modals')
    <x-mailcoach::modal :title="__mc('Load Unlayer template')" name="load-unlayer-template">
        <p class="mb-4">{!! __mc('You can load an <a class="text-blue-500" href="https://unlayer.com/templates" target="_blank">Unlayer template</a> by entering the slug.') !!}</p>

        <x-mailcoach::text-field label="Unlayer template" name="unlayer_template" />
        <p id="unlayer_template_error" class="form-error hidden mt-1" role="alert"></p>

        <div class="form-buttons">
            <x-mailcoach::button class="mt-auto ml-2" id="load-template" label="Load" type="button" />
            <x-mailcoach::button-cancel x-on:click.prevent="$store.modals.close('load-unlayer-template')" :label=" __mc('Cancel')" />
        </div>
    </x-mailcoach::modal>
@endpush
