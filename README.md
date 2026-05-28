# BadNet - Mạng xã hội cầu lông

## Giới thiệu

**BadNet** là mạng xã hội chuyên biệt dành cho cộng đồng cầu lông, phát triển bởi **Nhóm 6 - SA25-26_ClassN01**. Hệ thống cho phép người dùng kết nối, chia sẻ kinh nghiệm thi đấu, tìm bạn chơi, tham gia câu lạc bộ và sự kiện cầu lông.

Dự án sử dụng Laravel (PHP) kết hợp Blade, TypeScript, PLpgSQL và kiến trúc tách biệt backend/frontend, giúp dễ mở rộng và bảo trì.

## Kiến trúc thư mục

**src/**
- `app/`              : Logic ứng dụng (Models, Controllers, Services,...)
- `resources/`        : Blade templates, layout giao diện HTML/CSS
- `routes/`           : Định nghĩa route cho web/API
- `config/`           : File cấu hình ứng dụng, môi trường
- `database/`         : Migration, seed dữ liệu, PLpgSQL
- `lang/`             : Đa ngôn ngữ giao diện
- `public/`           : Nơi phục vụ static content
- `bootstrap/`        : File khởi tạo hệ thống
- Các file build/package manager: `composer.json`, `package.json`, `artisan`,...

## Chức năng nổi bật

1. **Đăng ký, đăng nhập, quản lý tài khoản**
2. **Tạo & cập nhật hồ sơ cá nhân, avatar**
3. **Đăng bài viết (text, hình ảnh, video), bình luận, tương tác**
4. **Kết bạn, kết nối & theo dõi người chơi/câu lạc bộ**
5. **Tạo, quản lý & tham gia câu lạc bộ cầu lông**
6. **Lịch sự kiện, đăng ký giải đấu, nhắc nhở lịch**
7. **Tìm kiếm thành viên, CLB, sự kiện thông minh**
8. **Thống kê, báo cáo hoạt động, bảng xếp hạng**
9. **Đa ngôn ngữ (có hỗ trợ Vietnamese/English)**
10. **Giao diện hiện đại, tối ưu trải nghiệm thiết bị di động**

## Yêu cầu hệ thống

- PHP ^8.x
- Composer
- Node.js & NPM
- PostgreSQL

## Cài đặt nhanh

```bash
git clone https://github.com/QlapMeowz1/SA25-26_ClassN01_Group-6.git
cd SA25-26_ClassN01_Group-6/src

composer install
npm install && npm run build

# Copy .env.example thành .env, chỉnh sửa cấu hình DB, mail,...
php artisan key:generate

php artisan migrate
php artisan serve
```

## Đóng góp & liên hệ

- Vui lòng tạo issue/pull request trên GitHub nếu phát hiện lỗi hoặc muốn đóng góp tính năng.
- Liên hệ nhóm phát triển qua profile GitHub hoặc thông tin trong dự án.

---

*BadNet - Kết nối đam mê cầu lông, cộng đồng lớn mạnh cùng phát triển!*
