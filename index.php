<?php
// ================= 1. KẾT NỐI DATABASE (VM DB: 192.168.60.4) =================
$db_host = "192.168.60.4";
$db_user = "educenter_user"; // Sửa lại user DB của bạn
$db_pass = "123456";         // Sửa lại mật khẩu DB của bạn
$db_name = "educenter_db";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $db_error = "Lỗi kết nối DB: " . $conn->connect_error;
} else {
    $conn->set_charset("utf8mb4");
}

// ================= 2. LẤY CONTAINER ID ĐỂ DEMO LOAD BALANCING =================
$container_id = gethostname();

// ================= 3. XỬ LÝ LƯU FORM ĐĂNG KÝ VÀO DB =================
$msg_status = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fullname'])) {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $course = trim($_POST['course_select']);

    if (!empty($fullname) && !empty($phone) && $conn && !$conn->connect_error) {
        $stmt = $conn->prepare("INSERT INTO registrations (fullname, phone, course_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullname, $phone, $course);
        if ($stmt->execute()) {
            $msg_status = "<div class='form-message success'>Cảm ơn $fullname! Đã lưu đăng ký vào DB thành công. Educenter sẽ liên hệ qua SĐT $phone!</div>";
        } else {
            $msg_status = "<div class='form-message error'>Lỗi lưu dữ liệu vào DB!</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educenter - Trung Tâm Đào Tạo Công Nghệ & Kỹ Năng</title>
    <style>
        /* ================= CẤU HÌNH GIAO DIỆN ĐỎ & ĐEN ================= */
        :root {
            --bg-body: #0a0a0c;
            --bg-card: #141418;
            --bg-card-hover: #1e1e24;
            --primary-red: #e11d48;
            --primary-red-hover: #be123c;
            --accent-red: #ff4d4d;
            --text-light: #f3f4f6;
            --text-muted: #9ca3af;
            --border-dark: #27272a;
            --shadow-red: 0 4px 20px rgba(225, 29, 72, 0.25);
            --radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-light);
            line-height: 1.6;
        }

        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* TOPBAR & BANNER DEMO LOAD BALANCING */
        .topbar {
            background-color: #000000;
            border-bottom: 1px solid var(--border-dark);
            padding: 8px 0;
            font-size: 0.85rem;
        }

        .topbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .clock-box { color: var(--accent-red); font-weight: 600; }
        
        .container-badge {
            background: rgba(225, 29, 72, 0.2);
            border: 1px solid var(--primary-red);
            padding: 3px 10px;
            border-radius: 4px;
            color: #fff;
            font-weight: bold;
        }

        header {
            background-color: rgba(10, 10, 12, 0.95);
            border-bottom: 1px solid var(--border-dark);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .logo { font-size: 1.8rem; font-weight: 800; color: #fff; letter-spacing: 1px; }
        .logo span { color: var(--primary-red); }

        .nav-menu { display: flex; gap: 25px; align-items: center; }
        .nav-link { font-weight: 500; color: var(--text-muted); transition: color 0.3s; }
        .nav-link:hover { color: var(--primary-red); }

        .btn-red {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-red-hover));
            color: #fff;
            padding: 10px 22px;
            border-radius: var(--radius);
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: var(--shadow-red);
            display: inline-block;
        }

        .btn-red:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(225, 29, 72, 0.4);
        }

        .menu-toggle { display: none; font-size: 1.8rem; cursor: pointer; color: #fff; }

        .hero {
            padding: 80px 0 60px 0;
            background: radial-gradient(circle at center, rgba(225, 29, 72, 0.15) 0%, rgba(10, 10, 12, 1) 70%);
            text-align: center;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(225, 29, 72, 0.15);
            border: 1px solid var(--primary-red);
            color: var(--accent-red);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .hero h1 { font-size: 3rem; font-weight: 800; margin-bottom: 20px; line-height: 1.2; }
        .hero h1 span { color: var(--primary-red); }
        .hero p { font-size: 1.15rem; color: var(--text-muted); max-width: 750px; margin: 0 auto 35px auto; }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 800px;
            margin: 40px auto 0 auto;
            background: var(--bg-card);
            padding: 25px;
            border-radius: var(--radius);
            border: 1px solid var(--border-dark);
        }

        .stat-item h3 { font-size: 2rem; color: var(--primary-red); }
        .stat-item p { font-size: 0.9rem; margin-bottom: 0; }

        .section { padding: 80px 0; }
        .section-title { text-align: center; font-size: 2.2rem; margin-bottom: 10px; }
        .section-title span { color: var(--primary-red); }
        .section-subtitle { text-align: center; color: var(--text-muted); margin-bottom: 40px; }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .course-card {
            background: var(--bg-card);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius);
            overflow: hidden;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .course-card:hover {
            transform: translateY(-6px);
            border-color: var(--primary-red);
            box-shadow: var(--shadow-red);
        }

        .course-body { padding: 22px; display: flex; flex-direction: column; flex-grow: 1; }
        .course-title { font-size: 1.25rem; margin-bottom: 12px; color: #fff; }
        .course-desc { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px; flex-grow: 1; }
        .course-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-dark); padding-top: 15px; }
        .course-price { font-size: 1.2rem; font-weight: 700; color: var(--accent-red); }

        .register-section {
            background: linear-gradient(180deg, var(--bg-body) 0%, #110507 100%);
            border-top: 1px solid var(--border-dark);
        }

        .form-box {
            max-width: 650px;
            margin: 0 auto;
            background: var(--bg-card);
            padding: 40px;
            border-radius: var(--radius);
            border: 1px solid var(--border-dark);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-light); }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: #0a0a0c;
            border: 1px solid var(--border-dark);
            border-radius: var(--radius);
            color: #fff;
            font-size: 1rem;
            outline: none;
        }

        .form-control:focus { border-color: var(--primary-red); }

        .form-message {
            margin-top: 20px;
            padding: 14px;
            border-radius: var(--radius);
            text-align: center;
            font-weight: 600;
        }

        .form-message.success { background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; color: #4ade80; }
        .form-message.error { background: rgba(225, 29, 72, 0.15); border: 1px solid var(--primary-red); color: var(--accent-red); }

        footer {
            background: #000000;
            border-top: 1px solid var(--border-dark);
            padding: 30px 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .menu-toggle { display: block; }
            .nav-menu {
                position: absolute; top: 70px; left: 0; width: 100%;
                background: var(--bg-card); border-bottom: 1px solid var(--border-dark);
                flex-direction: column; padding: 25px; gap: 20px; display: none;
            }
            .nav-menu.active { display: flex; }
            .hero h1 { font-size: 2.2rem; }
            .hero-stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- TOPBAR CHỨA KHU VỰC MINH CHỨNG CONTAINER ID & THỜI GIAN -->
    <div class="topbar">
        <div class="container topbar-content">
            <div>📍 Cơ sở: 268 Trần Hưng Đạo, TP. HCM | 📞 Hotline: 0988 123 456</div>
            <div class="container-badge">
                🐳 Handled by Container: <span style="color: #ff4d4d;"><?php echo $container_id; ?></span>
            </div>
            <div class="clock-box">🕒 Giờ hệ thống: <span id="currentTime">--:--:--</span></div>
        </div>
    </div>

    <!-- HEADER / NAVBAR -->
    <header>
        <div class="container navbar">
            <a href="#" class="logo">Edu<span>center</span></a>
            <div class="menu-toggle" id="menuToggle">☰</div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="#home" class="nav-link">Trang chủ</a></li>
                <li><a href="#courses" class="nav-link">Khóa học DB</a></li>
                <li><a href="#register" class="nav-link">Tư vấn</a></li>
                <li><a href="#register" class="btn-red">Đăng Ký Ngay</a></li>
            </ul>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero" id="home">
        <div class="container">
            <span class="hero-badge">🔥 ĐÀO TẠO CÔNG NGHỆ THỰC CHUYÊN SÂU</span>
            <h1>Đột Phá Sự Nghiệp Cùng <span>Educenter</span></h1>
            <p>Hệ thống khóa học Lập trình, Thiết kế & Kỹ năng chuẩn quốc tế. Tự động co giãn hạ tầng Auto-scaling gánh tải cao.</p>
            <a href="#courses" class="btn-red" style="font-size: 1.1rem; padding: 14px 36px;">Khám Phá Khóa Học</a>

            <div class="hero-stats">
                <div class="stat-item">
                    <h3>98%</h3>
                    <p>Học viên có việc làm</p>
                </div>
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Khóa học chuyên sâu</p>
                </div>
                <div class="stat-item">
                    <h3>5.000+</h3>
                    <p>Học viên đã tốt nghiệp</p>
                </div>
            </div>
        </div>
    </section>

    <!-- DANH SÁCH KHÓA HỌC TỪ MYSQL DATABASE -->
    <section class="section container" id="courses">
        <h2 class="section-title">Chương Trình <span>Đào Tạo (Dữ liệu MySQL)</span></h2>
        <p class="section-subtitle">Dữ liệu khóa học được load trực tiếp từ MySQL DB (192.168.60.4)</p>

        <div class="course-grid">
            <?php
            if (isset($db_error)) {
                echo "<p style='color:red; text-align:center; width:100%;'>$db_error</p>";
            } else {
                $sql = "SELECT * FROM courses";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        ?>
                        <div class="course-card">
                            <div class="course-body">
                                <h3 class="course-title"><?php echo htmlspecialchars($row['course_name']); ?></h3>
                                <p class="course-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                                <p style="font-size: 0.85rem; color: #9ca3af; margin-bottom: 10px;">⏱️ <strong>Thời lượng:</strong> <?php echo htmlspecialchars($row['duration']); ?></p>
                                <div class="course-footer">
                                    <span class="course-price"><?php echo htmlspecialchars($row['price']); ?></span>
                                    <a href="#register" class="btn-red" style="padding: 6px 16px; font-size: 0.85rem;">Đăng ký</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='text-align:center; width:100%;'>Chưa có khóa học nào trong Database!</p>";
                }
            }
            ?>
        </div>
    </section>

    <!-- FORM ĐĂNG KÝ TƯ VẤN (GỬI VÀO MYSQL DB) -->
    <section class="section register-section" id="register">
        <div class="container">
            <h2 class="section-title">Nhận <span>Tư Vấn Miễn Phí</span></h2>
            <p class="section-subtitle">Thông tin đăng ký sẽ được lưu trực tiếp vào cơ sở dữ liệu</p>

            <div class="form-box">
                <?php echo $msg_status; ?>
                
                <form action="index.php#register" method="POST">
                    <div class="form-group">
                        <label for="fullname">Họ và tên *</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Nhập họ và tên của bạn" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Số điện thoại *</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="Nhập số điện thoại" required>
                    </div>

                    <div class="form-group">
                        <label for="courseSelect">Khóa học quan tâm</label>
                        <select id="courseSelect" name="course_select" class="form-control">
                            <option value="DevOps Architecture">DevOps & Cloud Architecture</option>
                            <option value="Web Fullstack Node.js">Lập trình Web Fullstack Node.js</option>
                            <option value="Python Data Science">Phân tích Dữ liệu Python</option>
                            <option value="An ninh mang">Chuyên gia Bảo mật & An ninh mạng</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-red" style="width: 100%; padding: 14px;">Gửi Đăng Ký Ngay</button>
                </form>
            </div>
        </div>
    </section>

    <!-- FOOTER HIỂN THỊ THÔNG TIN SERVER -->
    <footer>
        <div class="container">
            <p>&copy; 2026 Educenter Academy. System running on Docker Compose Auto-Scaling.</p>
            <p style="margin-top: 5px; font-size: 0.8rem; color: var(--accent-red);">Server Hostname: <?php echo $container_id; ?></p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ĐỒNG HỒ THỜI GIAN THỰC
            function showTime() {
                const timeElement = document.getElementById('currentTime');
                if (timeElement) {
                    const now = new Date();
                    timeElement.innerHTML = now.toLocaleTimeString('vi-VN');
                }
            }
            showTime();
            setInterval(showTime, 1000);

            // TOGGLE MENU DI ĐỘNG
            const menuToggle = document.getElementById('menuToggle');
            const navMenu = document.getElementById('navMenu');
            if (menuToggle && navMenu) {
                menuToggle.addEventListener('click', function () {
                    navMenu.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
