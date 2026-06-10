<?php

namespace Database\Seeders;

use App\Models\Bet;
use App\Models\GameMatch;
use App\Models\Post;
use App\Models\User;
use App\Services\BetService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadmintonSeeder extends Seeder
{
    public function run()
    {
        $this->command->info(' Đang seed dữ liệu mạng xã hội cầu lông...');

        DB::table('comments')->delete();
        DB::table('posts')->delete();

        $users = User::query()->take(60)->get();

        if ($users->count() < 60) {
            $users = $users->merge(User::factory(60 - $users->count())->create());
        }

        $this->command->info(" Đã tạo {$users->count()} tài khoản.");

        $postCount = 0;
        foreach ($users as $user) {
            $posts = Post::factory(rand(4, 10))->create(['user_id' => $user->id]);
            $postCount += $posts->count();
        }

        $this->command->info("Đã tạo {$postCount} bài đăng kèm ảnh cầu lông.");
        $this->seedBetting($users);
        $this->command->info(' Hoàn thành! Mật khẩu mặc định: password');
    }

    private function seedBetting($users): void
    {
        $targetBets = 24;
        $existingBets = Bet::count();

        if ($existingBets >= $targetBets) {
            $this->command->info('ℹ Đã có dữ liệu betting, bỏ qua seed thêm.');
            return;
        }

        $matches = GameMatch::with(['player1', 'player2'])
            ->whereNotNull('player2_id')
            ->whereIn('status', ['open', 'scheduled', 'in_progress'])
            ->take(8)
            ->get();

        $missingMatches = max(0, 8 - $matches->count());
        for ($i = 0; $i < $missingMatches; $i++) {
            $player1 = $users->random();
            $player2 = $users->where('id', '!=', $player1->id)->random();
            $status = collect(['open', 'scheduled', 'in_progress'])->random();

            GameMatch::create([
                'player1_id' => $player1->id,
                'player2_id' => $player2->id,
                'status' => $status,
                'match_date' => now()->addDays(rand(1, 14)),
                'location' => 'Court ' . rand(1, 6),
            ]);
        }

        $matches = GameMatch::with(['player1', 'player2'])
            ->whereNotNull('player2_id')
            ->whereIn('status', ['open', 'scheduled', 'in_progress'])
            ->get();

        if ($matches->isEmpty()) {
            $this->command->warn(' Không có trận đấu để seed betting.');
            return;
        }

        $betService = app(BetService::class);
        $betsToCreate = $targetBets - $existingBets;
        $matchesToComplete = $matches->shuffle()->take(min(3, $matches->count()));

        for ($i = 0; $i < $betsToCreate; $i++) {
            $match = $matches->random();
            $bettor = $users->random();
            $betOn = rand(0, 1) === 0 ? $match->player1 : $match->player2;
            $amount = rand(20, 400);

            if ($bettor->virtual_coins < $amount) {
                $bettor->virtual_coins = $amount + rand(200, 800);
                $bettor->save();
            }

            try {
                $betService->placeBet($bettor, $match, $amount, $betOn->id);
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($matchesToComplete as $match) {
            $score1 = rand(12, 21);
            $score2 = rand(12, 21);
            if ($score1 === $score2) {
                $score1 += 2;
            }

            $winnerId = $score1 > $score2 ? $match->player1_id : $match->player2_id;

            $match->update([
                'status' => 'completed',
                'player1_score' => $score1,
                'player2_score' => $score2,
                'winner_id' => $winnerId,
                'match_date' => now()->subDays(rand(1, 10)),
            ]);

            $betService->settleBetsAfterMatch($match->fresh());
        }

        $this->command->info('Đã seed dữ liệu betting mẫu.');
    }
}