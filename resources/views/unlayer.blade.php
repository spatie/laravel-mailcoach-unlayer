@push('endHead')
    <script id="unlayer" src="https://editor.unlayer.com/embed.js" defer></script>
@endpush

<script>
    window.unlayerInitialized = false;

    document.getElementById('unlayer').addEventListener('load', initUnlayer);

    document.addEventListener('turbolinks:before-visit', confirmBeforeLeaveAndDestroyUnlayer);
    document.addEventListener("turbolinks:load", initUnlayer);
    window.addEventListener('beforeunload', confirmBeforeLeaveAndDestroyUnlayer);


    function initUnlayer() {
        if (window.unlayerInitialized) {
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
                document.querySelector('.layout-main form').submit();
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

        document.removeEventListener('turbolinks:before-visit', confirmBeforeLeaveAndDestroyUnlayer);
        document.removeEventListener("turbolinks:load", initUnlayer);
        window.removeEventListener('beforeunload', confirmBeforeLeaveAndDestroyUnlayer);
    }

</script>
<div>
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
    <button id="save" type="submit" class="button">
        <x-icon-label icon="fa-code" :text="__('Save content')"/>
    </button>
</div>

<x-replacer-help-texts />
