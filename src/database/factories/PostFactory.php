<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'user_id'     => User::inRandomOrder()->first()->id ?? 1,
            'content'     => $this->getNaturalBadmintonPost(),
            'image'       => $this->getRandomBadmintonImage(),
            'likes_count' => $this->faker->numberBetween(18, 650),
            'created_at'  => $this->faker->dateTimeBetween('-45 days', now()),
            'updated_at'  => now(),
        ];
    }

    private function getNaturalBadmintonPost()
    {
        $posts = [
            "Hôm nay mình smash rất ổn, cảm giác tự tin hẳn lên 🔥 Ai đang tập smash thì chia sẻ tiến bộ với mình nhé!",
            "Mọi người ai dùng Yonex Astrox 99 rồi cho mình xin cảm nhận thật lòng với. Mình đang phân vân có nên mua không.",
            "Luyện footwork buổi sáng xong mệt nhưng rất đã. Cảm giác di chuyển nhẹ nhàng hơn hẳn 💪",
            "Trận tối nay đánh quá hay: 21-14, 21-18. Một chiến thắng rất đẹp, vui cả buổi luôn 😄",
            "Ai có mẹo nào để đỡ cú smash mạnh không? Mình hay bị ép góc quá nên cần học thêm 😅",
            "Cầu lông đúng là môn thể thao nghiện thật. Tập xong là thấy khoẻ khoắn hẳn, đầu óc cũng nhẹ hơn.",
            "Cú drop shot hôm nay của mình khá ổn. Mọi người xem thử giúp mình có cần chỉnh gì thêm không?",
            "Mới đổi sang vợt Li-Ning, cảm giác cầm rất chắc tay. Ai chơi Li-Ning thì vào chia sẻ kinh nghiệm nhé!",
            "Cuối tuần rủ anh em đi tập cầu lông ở Sài Gòn, ai tham gia không? Cần thêm 3-4 người nữa.",
            "Từ ELO 900 lên 1650 chỉ trong 4 tháng. Cảm ơn mọi người đã góp ý và động viên mình rất nhiều!",
            "Trời mát thế này mà không ra sân tập thì phí quá. Ai ở gần quận 7 thì hẹn nhau đi thôi!",
            "Mẹo nhỏ: Khi tập smash, hãy chú ý thả lỏng vai để cú đánh tự nhiên và mạnh hơn.",
        ];

        $content = $this->faker->randomElement($posts);

        if ($this->faker->boolean(35)) {
            $content .= "\n\nẢnh: " . $this->getRandomBadmintonImage();
        }

        return $content . "\n\n#CầuLông #BadmintonVietnam #Smash #LuyệnCầu";
    }

    private function getRandomBadmintonImage()
    {
        $images = [
            'https://picsum.photos/id/1015/800/600',
            'https://picsum.photos/id/133/800/600',
            'https://picsum.photos/id/201/800/600',
            'https://picsum.photos/id/237/800/600',
            'https://picsum.photos/id/251/800/600',
            'https://picsum.photos/id/292/800/600',
            'https://picsum.photos/id/316/800/600',
            'https://picsum.photos/id/366/800/600',
            'https://picsum.photos/id/431/800/600',
            'https://picsum.photos/id/669/800/600',
            'https://picsum.photos/id/870/800/600',
            'https://picsum.photos/id/1016/800/600',
        ];

        return $images[array_rand($images)];
    }
}