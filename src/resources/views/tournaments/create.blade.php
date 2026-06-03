@extends('layout')

@section('title', 'Create Tournament - BadNet')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-gradient-to-r from-amber-50 via-white to-sky-50 px-6 py-6 sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-700">Tournament studio</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Create New Tournament</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Set the bracket, dates, and prize pool for your next community event.
            </p>
        </div>

        <div class="p-6 sm:p-8">
            <form method="POST" action="{{ route('tournaments.store') }}" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="name" class="text-sm font-semibold text-slate-800">Tournament Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                    @error('name') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label for="description" class="text-sm font-semibold text-slate-800">Description</label>
                    <textarea id="description" name="description" rows="6" maxlength="1000" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">{{ old('description') }}</textarea>
                    @error('description') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="start_date" class="text-sm font-semibold text-slate-800">Start Date & Time</label>
                        <input type="datetime-local" id="start_date" name="start_date" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                        @error('start_date') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="end_date" class="text-sm font-semibold text-slate-800">End Date & Time</label>
                        <input type="datetime-local" id="end_date" name="end_date" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                        <p class="text-sm text-slate-500">Optional. Leave blank to keep the window open-ended.</p>
                        @error('end_date') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="max_participants" class="text-sm font-semibold text-slate-800">Max Participants</label>
                        <input type="number" id="max_participants" name="max_participants" value="16" min="4" max="100" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                        @error('max_participants') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="prize_pool" class="text-sm font-semibold text-slate-800">Prize Pool</label>
                        <input type="number" id="prize_pool" name="prize_pool" min="0" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100">
                        <p class="text-sm text-slate-500">Optional. Use virtual coins or leave it at zero.</p>
                        @error('prize_pool') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-6">
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-amber-600">
                        Create Tournament
                    </button>
                    <a href="{{ route('tournaments.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
