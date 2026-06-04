<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SMASH Player Portal</title>
    @vite('resources/react/main.tsx')
    <style>
        html,
        body,
        #root {
            width: 100%;
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div
        id="root"
        data-initial-tab="{{ $initialTab ?? request('tab', 'feed') }}"
        data-user-name="{{ auth()->user()?->name ?? 'Nguyễn Văn A' }}"
        data-user-handle="{{ auth()->user()?->email ? \Illuminate\Support\Str::before(auth()->user()->email, '@') : 'nguyenvana' }}"
        data-user-rank="{{ auth()->user()?->rank ?? '#47' }}"
        data-is-admin="{{ auth()->check() && auth()->user()->isAdmin() ? 'true' : 'false' }}"
    ></div>
</body>
</html>
