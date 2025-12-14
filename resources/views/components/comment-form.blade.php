<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf

    @if($parentId)
        <input type="hidden" name="parent_id" value="{{ $parentId }}">
    @endif

    @if(!auth()->check() && config('sb-comments.allow_guests'))
        <div>
            <label for="guest_name" class="block text-sm font-medium text-gray-700">Name</label>
            <input
                type="text"
                id="guest_name"
                name="guest_name"
                value="{{ old('guest_name') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Your name"
                @if(config('sb-comments.guest_name_required')) required @endif
            >
            @error('guest_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    @endif

    <div>
        <textarea
            name="body"
            rows="3"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="{{ $placeholder }}"
            required
        >{{ old('body') }}</textarea>
        @error('body') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="flex justify-end">
        <button
            type="submit"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            {{ $submitText }}
        </button>
    </div>
</form>
