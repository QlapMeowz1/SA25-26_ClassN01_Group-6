<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class BadmintonAIService
{
    public function generatePostContent()
    {
        $prompt = "Viết một bài đăng mạng xã hội về cầu lông bằng **tiếng Việt thuần**, tự nhiên, gần gũi, nhiệt huyết như người Việt thật sự đăng. 
        Độ dài khoảng 60-120 từ. Sử dụng emoji hợp lý. 
        Tuyệt đối không được viết bằng tiếng Anh, không lorem ipsum, không câu Latin. 
        Chủ đề có thể là: cảm xúc khi chơi, kỹ thuật, vợt, trận đấu, lời mời tập chung, review vợt, v.v.";

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.85,
            'max_tokens' => 250,
        ]);

        return trim($result->choices[0]->message->content);
    }
}