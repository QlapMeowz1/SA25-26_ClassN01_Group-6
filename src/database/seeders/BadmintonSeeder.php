<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadmintonSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Đang seed dữ liệu mạng xã hội cầu lông...');

        DB::table('comments')->delete();
        DB::table('posts')->delete();

        // Tạo Users
        $users = User::query()->take(60)->get();

        if ($users->count() < 60) {
            $users = $users->merge(User::factory(60 - $users->count())->create());
        }

        $this->command->info("✅ Đã tạo {$users->count()} tài khoản.");

        // Tạo Posts
        $postCount = 0;
        foreach ($users as $user) {
            $posts = Post::factory(rand(4, 10))->create(['user_id' => $user->id]);
            $postCount += $posts->count();
        }

        $this->command->info("✅ Đã tạo {$postCount} bài đăng kèm ảnh cầu lông.");
        $this->command->info('🎉 Hoàn thành! Mật khẩu mặc định: password');
    }
}