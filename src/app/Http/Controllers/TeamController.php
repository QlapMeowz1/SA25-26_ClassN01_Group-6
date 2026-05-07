<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search', '');
        $levelFilter = $request->get('level', '');
        $locationFilter = $request->get('location', '');

        $myTeams = $user->teams()->paginate(10);

        $allTeamsQuery = Team::query();
        
        if (!empty($search)) {
            $allTeamsQuery->where('name', 'like', '%' . $search . '%')
                         ->orWhere('description', 'like', '%' . $search . '%');
        }
        
        if (!empty($levelFilter)) {
            $allTeamsQuery->where('level', $levelFilter);
        }

        if (!empty($locationFilter)) {
            $allTeamsQuery->where('location', 'like', '%' . $locationFilter . '%');
        }

        $allTeams = $allTeamsQuery->paginate(20);

        // Build suggested teams (same level or next level up)
        $suggestedTeams = $this->buildSuggestedTeams($user);

        return view('teams.index', compact('myTeams', 'allTeams', 'suggestedTeams', 'search', 'levelFilter', 'locationFilter'));
    }

    private function buildSuggestedTeams($user)
    {
        $sampleTeams = collect([
            (object)[
                'id' => 'sample-1',
                'name' => 'Saigon Smashers',
                'description' => 'Competitive intermediate team for regular matches and tournaments',
                'slogan' => 'Smashing through the competition',
                'location' => 'Saigon Sports Complex',
                'level' => 'Intermediate',
                'members_count' => 8,
                'max_members' => 20,
                'tags' => ['Competitive', 'Regular Matches', 'Social'],
                'leader' => (object)['name' => 'Coach Linh'],
                'is_sample' => true,
            ],
            (object)[
                'id' => 'sample-2',
                'name' => 'Hanoi Birdies',
                'description' => 'Advanced players focused on tactical excellence and tournament preparation',
                'slogan' => 'Bird by bird, we rise',
                'location' => 'Hanoi Central Arena',
                'level' => 'Advanced',
                'members_count' => 12,
                'max_members' => 25,
                'tags' => ['Advanced', 'Tournaments', 'Coaching'],
                'leader' => (object)['name' => 'Master Tuan'],
                'is_sample' => true,
            ],
            (object)[
                'id' => 'sample-3',
                'name' => 'Weekend Warriors',
                'description' => 'Casual friendly team perfect for beginners and recreational players',
                'slogan' => 'Fun first, winning second',
                'location' => 'Community Courts',
                'level' => 'Beginner',
                'members_count' => 5,
                'max_members' => 15,
                'tags' => ['Beginner Friendly', 'Casual', 'Social'],
                'leader' => (object)['name' => 'Alex Chen'],
                'is_sample' => true,
            ],
        ]);

        // Filter by user level preference
        return $sampleTeams->filter(function ($team) use ($user) {
            return strtolower($team->level) === strtolower($user->rank ?? 'Beginner') 
                || strtolower($team->level) === strtolower('Intermediate');
        })->take(6);
    }

    public function show(Team $team)
    {
        $members = $team->members()->paginate(10);

        return view('teams.show', compact('team', 'members'));
    }

    public function create()
    {
        return view('teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('logos'), $filename);
            $validated['logo'] = $filename;
        }

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'logo' => $validated['logo'] ?? null,
            'leader_id' => Auth::id(),
        ]);

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => Auth::id(),
            'role' => 'leader',
        ]);

        return redirect()->route('teams.show', $team->id)->with('success', 'Team created!');
    }

    public function join(Team $team)
    {
        $user = Auth::user();

        if ($team->hasMember($user->id)) {
            return back()->with('error', 'You are already a member of this team!');
        }

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        $team->members_count += 1;
        $team->save();

        return back()->with('success', 'Joined team successfully!');
    }

    public function leave(Team $team)
    {
        $user = Auth::user();

        if ($team->leader_id === $user->id) {
            return back()->with('error', 'Team leader cannot leave!');
        }

        TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->delete();

        $team->members_count -= 1;
        $team->save();

        return back()->with('success', 'Left team successfully!');
    }
}
