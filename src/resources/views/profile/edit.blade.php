@extends('layout')

@section('title', 'Edit Profile - BadNet')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-gradient-to-r from-sky-50 via-white to-emerald-50 px-6 py-6 sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-700">Profile studio</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Edit Profile</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Keep your profile fresh so teammates can find you quickly.
            </p>
        </div>

        <div class="p-6 sm:p-8">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2 md:col-span-2">
                        <label for="name" class="text-sm font-semibold text-slate-800">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                        @error('name') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="phone" class="text-sm font-semibold text-slate-800">Phone</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                        @error('phone') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="avatar" class="text-sm font-semibold text-slate-800">Profile Picture</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                        <p class="text-sm text-slate-500">Use a square image for the cleanest avatar crop.</p>
                        @error('avatar') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="bio" class="text-sm font-semibold text-slate-800">Bio</label>
                    <textarea id="bio" name="bio" rows="6" maxlength="500" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">{{ old('bio', auth()->user()->bio) }}</textarea>
                    @error('bio') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                </div>

                @if(auth()->user()->avatar)
                    <div class="rounded-[20px] border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                        <span class="font-semibold text-slate-900">Current avatar</span>
                        <div class="mt-3 flex items-center gap-4">
                            <img src="{{ asset('avatars/' . auth()->user()->avatar) }}" alt="Avatar" class="h-14 w-14 rounded-full object-cover ring-2 ring-white shadow-sm">
                            <span>Choose a new file to replace it.</span>
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-6">
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-sky-700">
                        Save Changes
                    </button>
                    <a href="{{ route('profile.show', auth()->id()) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
