@if (session('status'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
        <p class="font-semibold">Periksa kembali data berikut:</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('import_errors'))
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        <p class="font-semibold">Sebagian baris tidak diimport:</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach (session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
