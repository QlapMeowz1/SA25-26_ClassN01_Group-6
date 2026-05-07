@extends('layout')

@section('title', 'Send Challenge - BadNet')

@section('content')
<div class="form-container">
    <h2>Send Challenge</h2>

    <div class="challenge-create-actions">
        <a href="{{ route('challenges.index') }}" class="btn btn-secondary">Back to Arena</a>
        <form action="{{ route('challenges.quick') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="btn btn-primary">Quick Challenge</button>
        </form>
    </div>
    
    <form method="POST" action="{{ route('challenges.store') }}">
        @csrf

        <div class="form-group">
            <label for="opponent_id">Select Opponent</label>
            <select id="opponent_id" name="opponent_id">
                <option value="">-- Open challenge, let players request to join --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(old('opponent_id', $selectedOpponentId) == $user->id)>
                        {{ $user->name }} ({{ $user->rank }} - {{ $user->elo_rating }} ELO)
                    </option>
                @endforeach
            </select>
            @error('opponent_id') <span class="error-text">{{ $message }}</span> @enderror
            <p class="form-help">Leave blank to publish an open challenge. Other players can request to join and you choose who accepts.</p>
        </div>

        <div class="form-group">
            <label for="message">Message (Optional)</label>
            <textarea id="message" name="message" rows="5" maxlength="500" placeholder="Looking for intermediate players for a friendly 3-set match this weekend at Central Court"></textarea>
            @error('message') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Send Challenge</button>
        <a href="{{ route('challenges.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

    <div style="margin-top: 30px;">
        {{ $users->links() }}
    </div>
</div>
@endsection
