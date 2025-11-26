@php
    use Illuminate\Support\Facades\Storage;

    // On transforme la collection en tableau simple pour Alpine
    $items = $images->map(fn ($img) => [
        'id'    => $img->id,
        'url'   =>Storage::disk('s3')->url(ltrim($img->file_url, '/')),
        'title' => $img->title ?? '',
        // dd(Storage::disk('s3')->url($img->file_url))
    ])->values()->all();


    $startIndex = collect($items)->search(fn ($i) => $i['id'] === $currentId) ?? 0;
@endphp

<div
    x-data="{
        images: @js($items),
        index: {{ $startIndex }},
        next() { if (this.index < this.images.length - 1) this.index++ },
        prev() { if (this.index > 0) this.index-- }
    }"
    class="space-y-4"
>
    <div class="flex justify-between items-center">
        <button
            type="button"
            class="px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-40"
            x-on:click="prev"
            x-bind:disabled="index === 0"
        >
            ⬅ Précédente
        </button>

        <div class="text-sm text-gray-600">
            <span x-text="index + 1"></span>
            /
            <span x-text="images.length"></span>
        </div>

        <button
            type="button"
            class="px-3 py-1 text-sm rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-40"
            x-on:click="next"
            x-bind:disabled="index === images.length - 1"
        >
            Suivante ➡
        </button>
    </div>

    <div class="flex justify-center">
        <img
            x-bind:src="images[index].url"
            x-bind:alt="images[index].title"
            class="max-h-[70vh] max-w-full rounded-xl shadow-lg object-contain"
        >
    </div>

    <div class="text-center text-sm text-gray-700" x-text="images[index].title"></div>
</div>
