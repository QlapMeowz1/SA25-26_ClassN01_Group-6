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
        if ($myTeams->getCollection()->count() < 3) {
            $myTeams->setCollection(
                $myTeams->getCollection()
                    ->concat($this->buildSampleTeamCards()->take(3))
                    ->unique('name')
                    ->take(3)
                    ->values()
            );
        }

        $allTeamsQuery = Team::query();
        
        if (!empty($search)) {
            $allTeamsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        if (!empty($levelFilter)) {
            $allTeamsQuery->where('level', $levelFilter);
        }

        if (!empty($locationFilter)) {
            $allTeamsQuery->where('location', 'like', '%' . $locationFilter . '%');
        }

        $allTeams = $allTeamsQuery->paginate(20);
        $allTeams->setCollection(
            $allTeams->getCollection()->map(function ($team) {
                if (strtolower($team->name ?? '') === 'test') {
                    $team->name = 'Central City Club';
                    $team->slogan = 'Home of champions since 2024';
                    $team->description = 'A fresh squad ready to compete';
                    $team->members_count = 3;
                    $team->max_members = 20;
                    $team->location = 'Central Badminton Complex';
                    $team->level = 'All Levels Welcome';
                }

                return $team;
            })
        );

        if ($allTeams->getCollection()->count() < 9 && empty($search) && empty($levelFilter) && empty($locationFilter)) {
            $allTeams->setCollection(
                $allTeams->getCollection()
                    ->concat($this->buildSampleTeamCards())
                    ->unique('name')
                    ->take(12)
                    ->values()
            );
        }

        // Build suggested teams (same level or next level up)
        $suggestedTeams = $this->buildSuggestedTeams($user);

        return view('teams.index', compact('myTeams', 'allTeams', 'suggestedTeams', 'search', 'levelFilter', 'locationFilter'));
    }

    private function buildSampleTeamCards()
    {
        return collect([
            $this->sampleTeam('sample-team-1', 'Saigon Smashers', 'Smashing through the competition', 'Competitive intermediate team for regular matches and tournaments', 'Saigon Sports Complex', 'Intermediate', 8, 20, ['Competitive', 'Regular Matches', 'Tournaments']),
            $this->sampleTeam('sample-team-2', 'Hanoi Birdies', 'Bird by bird, we rise', 'Advanced players focused on tactical excellence and tournament preparation', 'Hanoi Central Court', 'Advanced', 12, 25, ['Advanced', 'Tournaments', 'Coaching']),
            $this->sampleTeam('sample-team-3', 'Weekend Warriors', 'Fun for everyone', 'Casual friendly team perfect for beginners and recreational players', 'District 7 Arena', 'Beginner', 5, 15, ['Beginner Friendly', 'Casual', 'Community']),
            $this->sampleTeam('sample-team-4', 'Hanoi Aces', 'Precision and power on every court', 'League-focused squad with weekly match analysis and drills', 'Hanoi Central Court', 'Advanced', 6, 16, ['Advanced', 'Competitive', 'League Play']),
            $this->sampleTeam('sample-team-5', 'Da Nang Dropshots', 'Control the rally', 'Technical group for net play, doubles rotation, and evening scrims', 'Da Nang Arena', 'Intermediate', 9, 18, ['Doubles', 'Net Play', 'Evening']),
            $this->sampleTeam('sample-team-6', 'Pune Aces', 'Calm court, sharp finish', 'Balanced roster for women singles, mixed doubles, and weekend tournaments', 'Pune Aces Hall', 'Professional', 14, 24, ['Professional', 'Mixed Doubles', 'Women Singles']),
            $this->sampleTeam('sample-team-7', 'Chennai Strikers', 'Serve fast, recover faster', 'High tempo players preparing for city league and ranking events', 'Chennai Sports Dome', 'Advanced', 11, 20, ['City League', 'Training', 'Ranking']),
            $this->sampleTeam('sample-team-8', 'Rookie Rally Club', 'Every point teaches', 'Beginner-first club with guided sessions and friendly challenges', 'Community Court 4', 'Beginner', 7, 20, ['Beginner', 'Coached', 'Friendly']),
            $this->sampleTeam('sample-team-9', 'Elite Net Lab', 'Small margins win matches', 'Invite-style group for advanced match review and tactical sessions', 'Central Badminton Lab', 'Professional', 10, 14, ['Invite', 'Analytics', 'Elite']),
        ]);
    }

    private function sampleTeam(string $id, string $name, string $slogan, string $description, string $location, string $level, int $members, int $max, array $tags)
    {
        return (object) [
            'id' => $id,
            'name' => $name,
            'slogan' => $slogan,
            'description' => $description,
            'location' => $location,
            'level' => $level,
            'members_count' => $members,
            'max_members' => $max,
            'tags' => json_encode($tags),
            'logo' => null,
            'is_sample' => true,
        ];
    }

    private function buildSuggestedTeams($user)
    {
        $sampleTeams = $this->buildSampleTeamCards()->map(function ($team) {
            $team->tags = json_decode($team->tags, true) ?: [];
            $team->leader = (object) ['name' => 'Community Lead'];
            return $team;
        });

        $sampleTeams = $sampleTeams->concat(collect([
            (object)[
                'id' => 'sample-1',
                'name' => 'Saigon Smashers',
                'description' => 'Competitive intermediate team for regular matches and tournaments',
                'slogan' => 'Smashing through the competition',
                'location' => 'Saigon Sports Complex',
                'level' => 'Intermediate',
                'members_count' => 8,
                'max_members' => 20,
                'tags' => ['Competitive', 'Regular Matches', 'Tournaments'],
                'leader' => (object)['name' => 'Coach Linh'],
                'is_sample' => true,
            ],
            (object)[
                'id' => 'sample-2',
                'name' => 'Hanoi Birdies',
                'description' => 'Advanced players focused on tactical excellence and tournament preparation',
                'slogan' => 'Bird by bird, we rise',
                'location' => 'Hanoi Central Court',
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
                'slogan' => 'Fun for everyone',
                'location' => 'District 7 Arena',
                'level' => 'Beginner',
                'members_count' => 5,
                'max_members' => 15,
                'tags' => ['Beginner Friendly', 'Casual & Social', 'Community'],
                'leader' => (object)['name' => 'Alex Chen'],
                'is_sample' => true,
            ],
            (object)[
                'id' => 'sample-4',
                'name' => 'Hanoi Aces',
                'description' => 'Precision and power on every court',
                'slogan' => 'Precision and power on every court',
                'location' => 'Hanoi Central Court',
                'level' => 'Advanced',
                'members_count' => 6,
                'max_members' => 16,
                'tags' => ['Advanced', 'Competitive', 'League Play'],
                'leader' => (object)['name' => 'RacketMaster'],
                'is_sample' => true,
            ],
        ]));

        // Filter by user level preference
        return $sampleTeams->filter(function ($team) use ($user) {
            return strtolower($team->level) === strtolower($user->rank ?? 'Beginner') 
                || strtolower($team->level) === strtolower('Intermediate');
        })->merge($sampleTeams->where('name', 'Hanoi Aces'))->unique('name')->take(6)->values();
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
