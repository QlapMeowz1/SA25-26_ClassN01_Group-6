@extends('layout')

@section('title', 'Create Team - BadNet')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-white to-sky-50 px-6 py-6 sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Team studio</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Create New Team</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Build a squad identity, add a logo, and invite players who fit your style.
            </p>
        </div>

        <div class="p-6 sm:p-8">
            <form method="POST" action="{{ route('teams.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="name" class="text-sm font-semibold text-slate-800">Team Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    @error('name') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label for="description" class="text-sm font-semibold text-slate-800">Description</label>
                    <textarea id="description" name="description" rows="6" maxlength="1000" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-100">{{ old('description') }}</textarea>
                    <p class="text-sm text-slate-500">Describe your team vibe, goals, and training cadence.</p>
                    @error('description') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label for="logo" class="text-sm font-semibold text-slate-800">Team Logo</label>
                    <input type="file" id="logo" name="logo" accept="image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                    <p class="text-sm text-slate-500">PNG or JPG works best for a crisp roster badge.</p>
                    @error('logo') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-6">
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700">
                        Create Team
                    </button>
                    <a href="{{ route('teams.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
